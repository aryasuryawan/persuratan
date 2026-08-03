<?php

namespace App\Console\Commands;

use App\Models\Disposisi;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class KirimReminderDisposisi extends Command
{
    protected $signature = 'disposisi:kirim-reminder';

    protected $description = 'Kirim reminder harian untuk disposisi yang deadline-nya terlewat tapi laporan follow-up belum terisi';

    public function handle(EmailService $emailService): int
    {
        if (Setting::get('reminder_disposisi_enabled') !== '1') {
            $this->info('Reminder disposisi nonaktif. Lewati.');
            return self::SUCCESS;
        }

        $jam = (int) Setting::get('reminder_disposisi_hour', 8);
        if ((int) now()->format('H') !== $jam) {
            $this->info("Bukan jam reminder (sekarang " . now()->format('H:i') . ", konfigurasi {$jam}:00). Lewati.");
            return self::SUCCESS;
        }

        $disposisiList = Disposisi::query()
            ->where('jenis', 'ke_pelaksana')
            ->whereNotNull('kepada_user_id')
            ->whereNotNull('tanggal_deadline')
            ->whereDate('tanggal_deadline', '<=', now()->toDateString())
            ->whereNull('completed_at')
            ->doesntHave('disposisiAnak')
            ->with(['kepadaUser', 'suratMasuk'])
            ->get()
            ->filter(function ($disposisi) {
                $surat = $disposisi->suratMasuk;
                if ($surat->followUps()->exists()) {
                    return false;
                }
                if ($surat->status && in_array($surat->status->kode, ['completed', 'finished'])) {
                    return false;
                }
                return true;
            });

        $terkirim = 0;
        $gagal = 0;

        foreach ($disposisiList as $disposisi) {
            $user = $disposisi->kepadaUser;
            $surat = $disposisi->suratMasuk;

            if (!$user || !$user->email || !$surat) {
                $gagal++;
                continue;
            }

            $result = $emailService->send(
                $user->email,
                "Reminder: Laporan Follow Up Disposisi - {$surat->nomor_agenda} - {$surat->perihal}",
                $this->buildBody($disposisi, $user),
                [
                    'type' => 'disposisi_reminder',
                    'reference_id' => $disposisi->id,
                    'recipient_type' => 'user',
                    'recipient_user_id' => $user->id,
                    'recipient_name' => $user->name,
                ]
            );

            if ($result['success']) {
                $terkirim++;
            } else {
                $gagal++;
                Log::warning('Reminder disposisi gagal dikirim', [
                    'disposisi_id' => $disposisi->id,
                    'error' => $result['error'] ?? '',
                ]);
            }
        }

        $this->info("Selesai. Perlu reminder: " . $disposisiList->count() . ", terkirim: {$terkirim}, gagal: {$gagal}.");

        return self::SUCCESS;
    }

    private function buildBody(Disposisi $disposisi, User $user): string
    {
        $surat = $disposisi->suratMasuk;
        $appName = Setting::get('smtp_from_name', Setting::get('app_name', config('app.name', 'SiMPEL')));
        $lembagaNama = Setting::get('lembaga_nama', 'Yayasan Dompet Dhuafa Republika');
        $appLogo = Setting::get('app_logo');
        $appLogoUrl = $appLogo ? \Illuminate\Support\Facades\Storage::disk('public')->url($appLogo) : null;
        $appUrl = config('app.url');
        $detailUrl = $appUrl . '/penerimaan-surat/' . $surat->uuid;

        $deadline = $disposisi->tanggal_deadline?->format('d/m/Y') ?? '-';
        $instruksi = !empty($disposisi->instruksi) ? implode(', ', $disposisi->instruksi) : '-';
        $logoHtml = $appLogoUrl ? '<img src="' . $appLogoUrl . '" alt="' . e($appName) . '" style="max-height: 60px; margin-bottom: 12px;">' : '';
        $message = Setting::get('reminder_disposisi_message', "Disposisi berikut melewati deadline tetapi laporan follow-up belum diisi. Mohon segera melengkapi.");

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 30px; border-radius: 12px 12px 0 0; text-align: center;">
        {$logoHtml}
        <h1 style="color: white; margin: 0; font-size: 24px;">{$appName}</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0;">{$lembagaNama}</p>
        <p style="color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 14px;">Reminder Deadline Follow Up Disposisi</p>
    </div>

    <div style="background: #f8fafc; padding: 30px; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; border-top: none;">
        <p style="margin: 0 0 16px; color: #1e293b;">Assalamu'alaikum, <strong>{$user->name}</strong>.</p>
        <p style="margin: 0 0 16px; color: #1e293b;">{$message}</p>

        <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500; width: 150px;">No. Agenda</td>
                    <td style="padding: 8px 0; color: #1e293b;"><strong>{$surat->nomor_agenda}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Perihal</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$surat->perihal}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Deadline</td>
                    <td style="padding: 8px 0; color: #dc2626;"><strong>{$deadline}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Instruksi</td>
                    <td style="padding: 8px 0; color: #1e293b;">{$instruksi}</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{$detailUrl}" style="display: inline-block; background: #dc2626; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                Isi Laporan Follow Up
            </a>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 12px;">
            <p style="margin: 0;">Email ini dikirim otomatis dari sistem {$appName}.</p>
            <p style="margin: 8px 0 0;">Mohon tidak membalas email ini secara langsung.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
