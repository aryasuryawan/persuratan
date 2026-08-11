<?php

namespace App\Services;

use App\Models\EmailNotificationLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailService
{
    public function send(string $to, string $subject, string $body, array $data = [], array $attachments = [], array $cc = []): array
    {
        $config = $this->getSmtpConfig();

        if (!$config['enabled']) {
            return [
                'success' => false,
                'error' => 'Notifikasi email dinonaktifkan. Silakan atur konfigurasi SMTP.',
            ];
        }

        $mailer = $this->createMailer($config);

        $log = EmailNotificationLog::create([
            'type' => $data['type'] ?? 'general',
            'reference_id' => $data['reference_id'] ?? null,
            'recipient_type' => $data['recipient_type'] ?? 'user',
            'recipient_user_id' => $data['recipient_user_id'] ?? null,
            'recipient_name' => $data['recipient_name'] ?? $to,
            'recipient_email' => $to,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'mailer_used' => $config['driver'],
        ]);

        try {
            Mail::mailer($mailer)->html($body, function ($message) use ($to, $subject, $config, $attachments, $cc) {
                $message->to($to)
                    ->subject($subject)
                    ->from($config['from_address'], $config['from_name']);

                if (!empty($cc)) {
                    $message->cc(array_values($cc));
                }
                
                foreach ($attachments as $attachment) {
                    $message->attach($attachment['path'], [
                        'as' => $attachment['name'] ?? basename($attachment['path']),
                        'mime' => $attachment['mime'] ?? null,
                    ]);
                }
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return ['success' => true, 'log_id' => $log->id];
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'driver' => $config['driver'],
            ]);

            return ['success' => false, 'error' => $e->getMessage(), 'log_id' => $log->id];
        }
    }

    public function sendToMultiple(array $recipients, string $subject, string $body, array $data = []): array
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $results[] = $this->send(
                $recipient['email'],
                $subject,
                $body,
                array_merge($data, [
                    'recipient_type' => $recipient['type'] ?? 'user',
                    'recipient_user_id' => $recipient['user_id'] ?? null,
                    'recipient_name' => $recipient['name'] ?? $recipient['email'],
                ])
            );
        }
        return $results;
    }

    private function getSmtpConfig(): array
    {
        $driver = Setting::get('smtp_driver', 'smtp') ?: 'smtp';

        return [
            'enabled' => (bool) Setting::get('smtp_enabled', false),
            'driver' => $driver,
            'host' => Setting::get('smtp_host'),
            'port' => (int) Setting::get('smtp_port', 587),
            'username' => Setting::get('smtp_username'),
            'password' => Setting::get('smtp_password'),
            'encryption' => Setting::get('smtp_encryption', 'tls'),
            'from_address' => Setting::get('smtp_from_address', config('mail.from.address')),
            'from_name' => Setting::get('smtp_from_name', config('mail.from.name')),
            'mailersend_api_key' => Setting::get('mailersend_api_key'),
            'sendgrid_api_key' => Setting::get('sendgrid_api_key'),
            'gmail_client_id' => Setting::get('gmail_client_id'),
            'gmail_client_secret' => Setting::get('gmail_client_secret'),
            'gmail_refresh_token' => Setting::get('gmail_refresh_token'),
        ];
    }

    /**
     * Semua provider memakai transport SMTP (native Laravel).
     * MailerSend/SendGrid/Gmail mendukung SMTP, sehingga tidak butuh package tambahan.
     * Untuk driver provider, host/port/username/password DIPAKSA sesuai ketentuan provider
     * (tidak peduli nilai smtp_host tersimpan dari driver lain).
     */
    private function createMailer(array $config): string
    {
        switch ($config['driver']) {
            case 'mailersend':
                $config['host'] = 'smtp.mailersend.net';
                $config['port'] = 587;
                $config['username'] = $config['mailersend_api_key'];
                $config['password'] = $config['mailersend_api_key'];
                $config['encryption'] = 'tls';
                break;
            case 'sendgrid':
                $config['host'] = 'smtp.sendgrid.net';
                $config['port'] = 587;
                $config['username'] = 'apikey';
                $config['password'] = $config['sendgrid_api_key'];
                $config['encryption'] = 'tls';
                break;
            case 'gmail':
                $config['host'] = 'smtp.gmail.com';
                $config['port'] = $config['port'] ?: 587;
                $config['encryption'] = $config['encryption'] ?: 'tls';
                break;
            case 'smtp':
            default:
                break;
        }

        if (empty($config['host']) || empty($config['from_address'])) {
            throw new \RuntimeException('Konfigurasi SMTP belum lengkap. Isi host dan from address di Pengaturan > Email/SMTP.');
        }

        $mailerName = 'dynamic_' . $config['driver'] . '_' . md5(serialize($config));

        config([
            "mail.mailers.{$mailerName}" => [
                'transport' => 'smtp',
                'host' => $config['host'],
                'port' => $config['port'],
                'username' => $config['username'],
                'password' => $config['password'],
                'encryption' => $config['encryption'] === 'none' ? null : $config['encryption'],
                'timeout' => 30,
                'verify_peer' => true,
            ],
        ]);

        return $mailerName;
    }

    public function testConnection(): array
    {
        $config = $this->getSmtpConfig();

        if (!$config['enabled']) {
            return ['success' => false, 'error' => 'Notifikasi email dinonaktifkan. Aktifkan dulu di Pengaturan > Email/SMTP.'];
        }

        $to = $config['username'] ?? $config['from_address'];
        if (!$to) {
            return ['success' => false, 'error' => 'Tidak ada alamat tujuan untuk email uji. Isi username/from address terlebih dahulu.'];
        }

        try {
            $mailer = $this->createMailer($config);

            Mail::mailer($mailer)->raw('Email ini adalah email uji dari sistem ' . config('app.name') . '. Jika Anda menerimanya, konfigurasi SMTP sudah benar.', function ($message) use ($config, $to) {
                $message->to($to)
                    ->subject('Email Uji - ' . config('app.name'))
                    ->from($config['from_address'], $config['from_name']);
            });

            return ['success' => true, 'message' => 'Email uji berhasil terkirim ke ' . $to];
        } catch (Throwable $e) {
            Log::error('SMTP test connection failed', [
                'error' => $e->getMessage(),
                'driver' => $config['driver'],
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}