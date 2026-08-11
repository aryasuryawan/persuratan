<?php

namespace App\Actions;

use App\Events\SuratStatusChanged;
use App\Models\Disposisi;
use App\Models\DisposisiAttachment;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BuatDisposisi
{
    public function __construct(
        protected EmailService $emailService
    ) {}

    public function execute(
        SuratMasuk $surat,
        User $dariUser,
        string $jenis,
        ?User $atasNamaPimpinan = null,
        ?User $kepadaUser = null,
        ?string $catatan = null,
        ?string $catatanPimpinan = null,
        ?string $tanggalDeadline = null,
        ?array $instruksi = null,
        ?array $disposisiKepada = null,
        array $lampiran = [],
        array $ccEmails = [],
    ): Disposisi {
        $statusKe = $jenis === 'ke_pelaksana' ? 'assigned' : 'handled_by_leadership';

        $disposisi = Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'dari_user_id' => $dariUser->id,
            'kepada_user_id' => $kepadaUser?->id,
            'jenis' => $jenis,
            'atas_nama_pimpinan_id' => $atasNamaPimpinan?->id,
            'catatan' => $catatan,
            'catatan_pimpinan' => $catatanPimpinan,
            'tanggal_deadline' => $tanggalDeadline,
            'instruksi' => $instruksi,
            'disposisi_kepada' => $disposisiKepada,
        ]);

        foreach ($lampiran as $file) {
            if (!$file instanceof UploadedFile || empty($file->getRealPath())) {
                continue;
            }

            try {
                $path = $file->store('disposisi', 'public');
                DisposisiAttachment::create([
                    'disposisi_id' => $disposisi->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            } catch (\Throwable $e) {
                continue;
            }
        }

        $oldStatusId = $surat->status_id;
        $surat->update(['status_id' => statusId($statusKe)]);

        event(new SuratStatusChanged(
            surat: $surat,
            statusDariId: $oldStatusId,
            statusKeId: statusId($statusKe),
            dilakukanOleh: $dariUser,
            atasNamaPimpinanId: $atasNamaPimpinan?->id,
            catatan: $catatan ?? $catatanPimpinan,
            disposisiId: $disposisi->id,
        ));

        $this->sendNotifications($surat, $disposisi, $dariUser, $atasNamaPimpinan, $kepadaUser, $disposisiKepada, $catatan, $catatanPimpinan, $instruksi, $tanggalDeadline, $ccEmails);

        return $disposisi;
    }

    private function sendNotifications(
        SuratMasuk $surat,
        Disposisi $disposisi,
        User $dariUser,
        ?User $atasNamaPimpinan,
        ?User $kepadaUser,
        ?array $disposisiKepada,
        ?string $catatan,
        ?string $catatanPimpinan,
        ?array $instruksi,
        ?string $tanggalDeadline,
        array $ccEmails = []
    ): void {
        $recipients = $this->buildRecipients($surat, $kepadaUser, $disposisiKepada);
        
        if (empty($recipients)) {
            return;
        }

        $jabatan = strtoupper(trim($atasNamaPimpinan?->jabatan ?? $dariUser->jabatan ?? 'PIMPINAN'));
        $subject = "DISPOSISI {$jabatan}: {$surat->perihal}";
        $body = $this->buildEmailBody($surat, $disposisi, $dariUser, $atasNamaPimpinan, $kepadaUser, $disposisiKepada, $catatan, $catatanPimpinan, $instruksi, $tanggalDeadline);

        $cc = $this->buildCcEmails($recipients, $ccEmails);

        // Fetch surat lampiran untuk attachment
        $attachments = [];
        if ($surat->lampiran) {
            foreach ($surat->lampiran as $file) {
                $path = \Illuminate\Support\Facades\Storage::disk('public')->path($file['stored_path']);
                if (\Illuminate\Support\Facades\File::exists($path)) {
                    $attachments[] = [
                        'path' => $path,
                        'name' => $file['original_name'] ?? basename($file['stored_path']),
                        'mime' => $file['mime_type'] ?? null,
                    ];
                }
            }
        }

        foreach ($recipients as $recipient) {
            $this->emailService->send(
                $recipient['email'],
                $subject,
                $body,
                [
                    'type' => 'disposisi',
                    'reference_id' => $disposisi->id,
                    'recipient_type' => $recipient['type'],
                    'recipient_user_id' => $recipient['user_id'] ?? null,
                    'recipient_name' => $recipient['name'],
                ],
                $attachments,
                $cc
            );
        }
    }

    private function buildCcEmails(array $recipients, array $ccEmails): array
    {
        $recipientEmails = array_map(fn($r) => strtolower(trim($r['email'])), $recipients);
        $cc = [];

        foreach ($ccEmails as $email) {
            $email = strtolower(trim((string) $email));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (in_array($email, $recipientEmails)) {
                continue;
            }
            $cc[] = $email;
        }

        return array_values(array_unique($cc));
    }

    private function buildRecipients(SuratMasuk $surat, ?User $kepadaUser, ?array $disposisiKepada): array
    {
        $recipients = [];

        if ($kepadaUser && $kepadaUser->email) {
            $recipients[] = [
                'email' => $kepadaUser->email,
                'name' => $kepadaUser->name,
                'type' => 'user',
                'user_id' => $kepadaUser->id,
            ];
        }

        if (!empty($disposisiKepada)) {
            $userNames = User::where('is_active', true)->pluck('email', 'name')->toArray();
            
            foreach ($disposisiKepada as $item) {
                if (isset($userNames[$item])) {
                    $user = User::where('name', $item)->where('is_active', true)->first();
                    if ($user && $user->email) {
                        $recipients[] = [
                            'email' => $user->email,
                            'name' => $user->name,
                            'type' => 'user',
                            'user_id' => $user->id,
                        ];
                    }
                } else {
                    $name = $item;
                    $email = '';
                    if (preg_match('/^(.+)\s*<([^>]+)>$/', $item, $m)) {
                        $name = trim($m[1]);
                        $email = trim($m[2]);
                    } elseif (filter_var($item, FILTER_VALIDATE_EMAIL)) {
                        $email = $item;
                    }
                    if ($email === '') {
                        continue;
                    }
                    $recipients[] = [
                        'email' => $email,
                        'name' => $name,
                        'type' => 'freetext',
                        'user_id' => null,
                    ];
                }
            }
        }

        $uniqueRecipients = [];
        $seenEmails = [];
        foreach ($recipients as $recipient) {
            if (!in_array($recipient['email'], $seenEmails)) {
                $uniqueRecipients[] = $recipient;
                $seenEmails[] = $recipient['email'];
            }
        }

        return $uniqueRecipients;
    }

    private function buildEmailBody(
        SuratMasuk $surat,
        Disposisi $disposisi,
        User $dariUser,
        ?User $atasNamaPimpinan,
        ?User $kepadaUser,
        ?array $disposisiKepada,
        ?string $catatan,
        ?string $catatanPimpinan,
        ?array $instruksi,
        ?string $tanggalDeadline
    ): string {
        $appName = \App\Models\Setting::get('smtp_from_name', \App\Models\Setting::get('app_name', config('app.name', 'SiMPEL')));
        $lembagaNama = \App\Models\Setting::get('lembaga_nama', 'Yayasan Dompet Dhuafa Republika');
        $appLogo = \App\Models\Setting::get('app_logo');
        $appLogoUrl = $appLogo ? \Illuminate\Support\Facades\Storage::disk('public')->url($appLogo) : null;
        $appUrl = config('app.url');

        $nomorAgenda = $surat->nomor_agenda;
        $nomorSurat = $surat->nomor_surat ?? '-';
        $perihal = $surat->perihal;
        $pengirim = $surat->pengirim;
        $tanggalSurat = $surat->tanggal_surat?->format('d/m/Y') ?? '-';
        $klasifikasi = $surat->klasifikasi->nama ?? '-';
        $dariNama = $dariUser->name;
        $atasNama = $atasNamaPimpinan?->name ?? 'Pimpinan';
        $disposisiKepadaList = !empty($disposisiKepada) ? implode(', ', $disposisiKepada) : '-';
        $instruksiList = !empty($instruksi) ? implode(', ', $instruksi) : '-';
        $kepadaNama = $kepadaUser?->name ?? $disposisiKepadaList;
        $batasWaktu = $tanggalDeadline ? \Carbon\Carbon::parse($tanggalDeadline)->format('d/m/Y') : '-';
        $detailUrl = $appUrl . '/penerimaan-surat/' . $surat->uuid;
        $followUpUrl = $appUrl . '/follow-up/' . $disposisi->uuid;
        $catatanHtml = nl2br(e($catatan ?? ''));
        $catatanPimpinanHtml = nl2br(e($catatanPimpinan ?? ''));
        $showCatatan = !empty($catatan) || !empty($catatanPimpinan);
        $catatanSection = $showCatatan ? <<<HTML
        <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <h2 style="margin: 0 0 16px; color: #1e293b; font-size: 18px;">Catatan</h2>
            <div style="margin-bottom: 16px;">
                <div style="color: #64748b; font-weight: 500; margin-bottom: 4px;">Isi Disposisi</div>
                <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border-left: 3px solid #2563eb;">{$catatanHtml}</div>
            </div>
            <div>
                <div style="color: #64748b; font-weight: 500; margin-bottom: 4px;">Catatan Pimpinan</div>
                <div style="background: #fef3c7; padding: 12px; border-radius: 6px; border-left: 3px solid #f59e0b;">{$catatanPimpinanHtml}</div>
            </div>
        </div>
        HTML : '';

        $logoHtml = $appLogoUrl ? '<img src="' . $appLogoUrl . '" alt="' . e($appName) . '" style="max-height: 60px; margin-bottom: 12px;">' : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 30px; border-radius: 12px 12px 0 0; text-align: center;">
        {$logoHtml}
        <h1 style="color: white; margin: 0; font-size: 24px;">{$appName}</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0;">{$lembagaNama}</p>
        <p style="color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 14px;">Notifikasi Disposisi Baru</p>
    </div>
    
    <div style="background: #f8fafc; padding: 30px; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; border-top: none;">
        <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <h2 style="margin: 0 0 16px; color: #1e293b; font-size: 18px;">Detail Surat</h2>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500; width: 140px;">No. Agenda</td>
                    <td style="padding: 8px 0; color: #1e293b;"><strong>{$nomorAgenda}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">No. Surat</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$nomorSurat}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Perihal</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$perihal}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Pengirim</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$pengirim}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Tanggal Surat</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$tanggalSurat}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Klasifikasi</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$klasifikasi}</td>
                </tr>
            </table>
        </div>

        <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <h2 style="margin: 0 0 16px; color: #1e293b; font-size: 18px;">Detail Disposisi</h2>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500; width: 140px;">Dari</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$dariNama}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Atas Nama</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$atasNama}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Kepada</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$kepadaNama}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Disposisi Kepada</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$disposisiKepadaList}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Instruksi</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$instruksiList}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Batas Waktu</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$batasWaktu}</td>
                </tr>
            </table>
        </div>

        {$catatanSection}

        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{$followUpUrl}" style="display: inline-block; background: #16a34a; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; margin-bottom: 12px;">
                Lapor Follow Up Disposisi
            </a>
            <div>
            <a href="{$detailUrl}" style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                Lihat Detail Surat
            </a>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 12px;">
            <p style="margin: 0;">Email ini dikirim secara otomatis dari sistem {$appName}.</p>
            <p style="margin: 8px 0 0;">Mohon tidak membalas email ini secara langsung.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}