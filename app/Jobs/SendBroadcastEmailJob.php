<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| SendBroadcastEmailJob
|--------------------------------------------------------------------------
| ★★★ BARU: kirim satu email broadcast (ke satu company) sebagai job queue —
| dijalankan di background oleh `php artisan queue:work`, TIDAK lagi
| memblokir/menunda halaman web sampai semua company selesai dikirimi.
|
| Ini memecah proses "Broadcast All" yang tadinya satu loop besar & sinkron
| di dalam BroadcastController::send() menjadi banyak job kecil yang bisa
| diproses satu-satu (atau paralel, kalau worker-nya lebih dari satu),
| dengan retry otomatis kalau gagal (misal SMTP timeout sesaat).
|--------------------------------------------------------------------------
*/

class SendBroadcastEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Percobaan ulang maksimal kalau job gagal (misal SMTP down sesaat).
     */
    public int $tries = 3;

    /**
     * Jeda (detik) sebelum job dicoba ulang setelah gagal.
     */
    public int $backoff = 30;

    /**
     * Batas waktu maksimal satu job boleh berjalan (detik), supaya job yang
     * "nyangkut" (misal SMTP hang) tidak memblokir worker selamanya.
     */
    public int $timeout = 90;

    protected string $kodeCompany;
    protected string $companyName;
    protected array $emails;
    protected array $ccEmails;
    protected string $subject;
    protected string $body;

    /** @var array<int, array{path: string, name: string}> */
    protected array $attachments;

    protected string $senderEmail;
    protected string $senderSmtpPassword;
    protected string $senderName;
    protected string $kategoriLabel;

    /**
     * @param array<int, string> $emails
     * @param array<int, string> $ccEmails
     * @param array<int, array{path: string, name: string}> $attachments Path relatif di disk 'local' + nama file asli
     */
    public function __construct(
        string $kodeCompany,
        string $companyName,
        array $emails,
        array $ccEmails,
        string $subject,
        string $body,
        array $attachments,
        string $senderEmail,
        string $senderSmtpPassword,
        string $senderName,
        string $kategoriLabel
    ) {
        $this->kodeCompany        = $kodeCompany;
        $this->companyName        = $companyName;
        $this->emails              = $emails;
        $this->ccEmails            = $ccEmails;
        $this->subject             = $subject;
        $this->body                = $body;
        $this->attachments         = $attachments;
        $this->senderEmail         = $senderEmail;
        $this->senderSmtpPassword  = $senderSmtpPassword;
        $this->senderName          = $senderName;
        $this->kategoriLabel       = $kategoriLabel;
    }

    public function handle(): void
    {
        // Kalau batch-nya sudah dibatalkan (misal user klik "cancel" di masa depan),
        // job yang belum sempat jalan tidak usah diproses lagi.
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        // ★★★ Konfigurasi SMTP + suntik SSL — persis strukturnya sama seperti
        // EmailController::send() / BroadcastController versi sinkron sebelumnya,
        // yang sudah terbukti berhasil terkirim. ★★★
        config([
            'mail.mailers.smtp.host'       => 'mail.harmoniputra.com',
            'mail.mailers.smtp.port'       => 465,
            'mail.mailers.smtp.encryption' => 'ssl',

            'mail.mailers.smtp.username'   => $this->senderEmail,
            'mail.mailers.smtp.password'   => $this->senderSmtpPassword,

            'mail.from.address'            => $this->senderEmail,
            'mail.from.name'               => $this->senderName,
        ]);

        Log::info('[BroadcastJob] Mulai kirim', [
            'kode_company' => $this->kodeCompany,
            'to'           => $this->emails,
            'cc'           => $this->ccEmails,
            'subject'      => $this->subject,
        ]);

        Mail::purge('smtp');

        $mailer    = Mail::mailer('smtp');
        $transport = $mailer->getSymfonyTransport();

        if (method_exists($transport, 'getStream')) {

            $stream = $transport->getStream();

            if (method_exists($stream, 'setStreamOptions')) {

                $stream->setStreamOptions([
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ]);
            }
        }

        $emails      = $this->emails;
        $ccEmails    = $this->ccEmails;
        $subject     = $this->subject;
        $body        = $this->body;
        $attachments = $this->attachments;
        $senderEmail = $this->senderEmail;
        $senderName  = $this->senderName;

        $mailer->html($body, function ($message) use ($emails, $ccEmails, $subject, $attachments, $senderEmail, $senderName) {

            // ★★★ BARU: penerima company + CC lama sekarang digabung dan disembunyikan
            // di BCC (bukan lagi terlihat di To/Cc, supaya alamat email penerima lain
            // tidak saling terekspos satu sama lain). Kolom "To" yang terlihat di email
            // sekarang cuma diisi alamat SI PENGIRIM sendiri (misal APO2 broadcast, To =
            // apo2@..., kalau Finance yang broadcast, To = email finance itu sendiri) —
            // supaya email tetap terlihat wajar/rapi di client email penerima, bukan
            // kosong. Alamat asli penerima tetap dapat email seperti biasa, cuma
            // posisinya di header BCC (invisible ke penerima lain). ★★★
            $bccEmails = array_values(array_unique(array_merge($emails, $ccEmails)));

            $message
                ->from($senderEmail, $senderName)
                ->to($senderEmail, $senderName)
                ->bcc($bccEmails)
                ->subject($subject);

            foreach ($attachments as $attachment) {

                $fullPath = Storage::disk('local')->path($attachment['path']);

                if (file_exists($fullPath)) {
                    $message->attach($fullPath, [
                        'as' => $attachment['name'],
                    ]);
                }
            }
        });

        Log::info('[BroadcastJob] Berhasil kirim', [
            'kode_company' => $this->kodeCompany,
        ]);

        // Log ke email_logs supaya ikut muncul di "Riwayat Email Terkirim" di Dashboard.
        // Ini BARU dicatat setelah benar-benar terkirim (bukan waktu di-dispatch),
        // jadi kalau job gagal & masih retry, belum tercatat sebagai sukses.
        DB::table('email_logs')->insert([
            'kode_company'   => $this->kodeCompany,
            'template_name'  => $this->kategoriLabel === ''
                ? 'Broadcast All'
                : 'Broadcast All (Kategori: ' . $this->kategoriLabel . ')',
            'subject'        => $subject,
            'company_name'   => $this->companyName,
            'recipient'      => implode(', ', $emails),
            'total_case'     => 0,
            'aging_filter'   => null,
            'sent_at'        => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    /**
     * Dipanggil otomatis oleh Laravel kalau job gagal terus sampai $tries habis.
     * Dicatat sebagai baris "gagal" di email_logs supaya kelihatan di Riwayat,
     * bukan cuma diam-diam hilang.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[BroadcastJob] GAGAL permanen (sudah dicoba ulang max, tetap gagal)', [
            'kode_company' => $this->kodeCompany,
            'to'           => $this->emails,
            'message'      => $exception->getMessage(),
        ]);

        DB::table('email_logs')->insert([
            'kode_company'   => $this->kodeCompany,
            'template_name'  => 'Broadcast All (GAGAL: ' . $exception->getMessage() . ')',
            'subject'        => $this->subject,
            'company_name'   => $this->companyName,
            'recipient'      => implode(', ', $this->emails),
            'total_case'     => 0,
            'aging_filter'   => null,
            // ★★★ DIUBAH CLAUDE: sebelumnya 'sent_at' => null, tapi kolom sent_at di
            // tabel email_logs ternyata NOT NULL — insert log kegagalan malah ikut
            // gagal (error kedua menutupi error asli). Sekarang diisi now() (waktu
            // job dinyatakan gagal), statusnya tetap jelas kelihatan GAGAL dari
            // template_name. ★★★
            'sent_at'        => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}