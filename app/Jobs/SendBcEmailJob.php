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
use App\Support\CompanyGroup; // [DITAMBAHKAN] kode gabungan PO Labor (mis. ELS COMPUTER)

/*
|--------------------------------------------------------------------------
| SendBcEmailJob
|--------------------------------------------------------------------------
| ★★★ BARU: kirim satu email BC Email (Pembayaran / Reminder Invoice / PO
| Labor) sebagai job queue — dijalankan di background oleh
| `php artisan queue:work`, supaya bisa dibatch 50 email per jam tanpa
| memblokir halaman web.
|
| Isinya sama persis konsepnya dengan SendBroadcastEmailJob, cuma beda
| sumber data (BC Email pakai template dari email_templates + tabel
| pembayaran/reminder/apo, bukan broadcast manual), dan setelah berhasil
| kirim, job ini juga yang bertanggung jawab update `sent_at` di tabel
| sumbernya (supaya data yang masih di batch tunggu TIDAK ke-mark sent_at
| duluan sebelum benar-benar terkirim).
|--------------------------------------------------------------------------
*/

class SendBcEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 90;

    protected string $kodeCompany;
    protected string $companyName;
    protected array $emails;
    protected array $ccEmails;
    protected array $bccEmails;
    protected string $subject;
    protected string $html;

    /** @var array<int, array{path: string, name: string, mime?: string}> */
    protected array $attachments;

    protected string $senderEmail;
    protected string $senderSmtpPassword;
    protected string $senderName;
    protected string $templateName;
    protected int $totalCase;
    protected ?string $agingFilter;

    /**
     * Jenis monitoring job ini: 'pembayaran' | 'reminder' | 'apo' | 'mpr'.
     * Dipakai buat nentuin tabel & cara update `sent_at` yang benar
     * setelah email ini berhasil terkirim.
     */
    protected string $jenisType;

    /**
     * [DITAMBAHKAN] Waktu job ini dibuat (saat dispatch). Dipakai guard
     * idempoten khusus MPR: MPR boleh dikirim ulang, jadi guard-nya bukan
     * "sudah pernah sent_at", tapi "sent_at-nya terisi SETELAH job ini dibuat".
     */
    protected string $dispatchedAt;

    public function __construct(
        string $kodeCompany,
        string $companyName,
        array $emails,
        array $ccEmails,
        array $bccEmails,
        string $subject,
        string $html,
        array $attachments,
        string $senderEmail,
        string $senderSmtpPassword,
        string $senderName,
        string $templateName,
        int $totalCase,
        ?string $agingFilter,
        string $jenisType
    ) {
        $this->kodeCompany        = $kodeCompany;
        $this->companyName        = $companyName;
        $this->emails              = $emails;
        $this->ccEmails            = $ccEmails;
        $this->bccEmails           = $bccEmails;
        $this->subject             = $subject;
        $this->html                = $html;
        $this->attachments         = $attachments;
        $this->senderEmail         = $senderEmail;
        $this->senderSmtpPassword  = $senderSmtpPassword;
        $this->senderName          = $senderName;
        $this->templateName        = $templateName;
        $this->totalCase           = $totalCase;
        $this->agingFilter         = $agingFilter;
        $this->jenisType           = $jenisType;
        $this->dispatchedAt        = now()->toDateTimeString();
    }

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        // [DITAMBAHKAN] Guard idempoten — cegah kirim ULANG kalau job ini
        // di-retry (misal exception kejadian SETELAH email sukses terkirim
        // tapi SEBELUM sent_at/email_logs sempat ke-update di percobaan
        // sebelumnya — entah karena DB sempat putus sesaat, atau worker
        // restart di tengah proses). Kalau data sumbernya utk company ini
        // ternyata SUDAH ke-mark sent_at (artinya percobaan sebelumnya
        // sebenarnya SUDAH berhasil kirim), job ini berhenti di sini —
        // tidak kirim email lagi.
        if ($this->alreadySent()) {
            Log::warning('[BcEmailJob] Dilewati (retry) — company ini sudah pernah terkirim', [
                'jenis'        => $this->jenisType,
                'kode_company' => $this->kodeCompany,
            ]);
            return;
        }

        // Konfigurasi SMTP — persis sama seperti EmailController::send() versi sinkron.
        config([
            'mail.mailers.smtp.host'       => 'mail.harmoniputra.com',
            'mail.mailers.smtp.port'       => 465,
            'mail.mailers.smtp.encryption' => 'ssl',

            'mail.mailers.smtp.username'   => $this->senderEmail,
            'mail.mailers.smtp.password'   => $this->senderSmtpPassword,

            'mail.from.address'            => $this->senderEmail,
            'mail.from.name'               => $this->senderName,
        ]);

        Log::info('[BcEmailJob] Mulai kirim', [
            'jenis'        => $this->jenisType,
            'kode_company' => $this->kodeCompany,
            'to'           => $this->emails,
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
        $bccEmails   = $this->bccEmails;
        $subject     = $this->subject;
        $attachments = $this->attachments;
        $senderEmail = $this->senderEmail;
        $senderName  = $this->senderName;

        $mailer->html($this->html, function ($message) use (
            $emails,
            $ccEmails,
            $bccEmails,
            $subject,
            $attachments,
            $senderEmail,
            $senderName
        ) {
            $message
                ->from($senderEmail, $senderName)
                ->to($emails)
                ->cc($ccEmails)
                ->bcc($bccEmails)
                ->subject($subject);

            foreach ($attachments as $attachment) {

                $fullPath = Storage::disk('local')->path($attachment['path']);

                if (file_exists($fullPath)) {
                    $message->attach($fullPath, array_filter([
                        'as'   => $attachment['name'],
                        'mime' => $attachment['mime'] ?? null,
                    ]));
                }
            }
        });

        Log::info('[BcEmailJob] Berhasil kirim', ['kode_company' => $this->kodeCompany]);

        // Update sent_at di tabel sumber — HANYA setelah beneran terkirim,
        // supaya data yang masih nunggu di batch belakangan tetap whereNull('sent_at')
        // dan tidak ikut ke-skip pas batch berikutnya jalan.
        switch ($this->jenisType) {

            case 'pembayaran':
                DB::table('pembayaran')
                    ->where('kode_company', $this->kodeCompany)
                    ->whereNull('sent_at')
                    ->update(['sent_at' => now()]);
                break;

            case 'reminder':
                DB::table('reminder')
                    ->where(DB::raw('LEFT(TRIM(kode_company),4)'), $this->kodeCompany)
                    ->whereNull('sent_at')
                    ->update(['sent_at' => now()]);
                break;

            case 'apo':
                // [DIUBAH] whereIn: kode gabungan (ELS COMPUTER = 630) menandai
                // SEMUA case cabangnya (630, 633, dst) sebagai terkirim.
                DB::table('apo')
                    ->whereIn(DB::raw('LEFT(case_id,3)'), CompanyGroup::apoCodes($this->kodeCompany))
                    ->whereNull('sent_at')
                    ->update(['sent_at' => now()]);
                break;

            // [DITAMBAHKAN] MPR — sama seperti jalur kirim langsung di EmailController
            case 'mpr':
                DB::table('mpr_datas')
                    ->where('kode_company', $this->kodeCompany)
                    ->whereNull('sent_at')
                    ->update(['sent_at' => now()]);
                break;
        }

        // Log ke email_logs — dicatat SETELAH terkirim (bukan waktu di-dispatch),
        // konsisten dengan pola SendBroadcastEmailJob.
        DB::table('email_logs')->insert([
            'kode_company'  => $this->kodeCompany,
            'template_name' => $this->templateName,
            'subject'       => $subject,
            'company_name'  => $this->companyName,
            'recipient'     => implode(', ', $emails),
            'total_case'    => $this->totalCase,
            'aging_filter'  => $this->agingFilter,
            'sent_at'       => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    // [DITAMBAHKAN] Cek apakah data sumber untuk company ini SUDAH ke-mark
    // sent_at (artinya sudah pernah beneran terkirim di percobaan job
    // sebelumnya). Query whereNull('sent_at') dipakai konsisten dengan yang
    // dipakai di bagian update sent_at di handle(). Dianggap "sudah terkirim"
    // HANYA kalau memang ada baris data untuk company ini DAN semuanya sudah
    // ke-mark sent_at — bukan sekadar karena kebetulan tidak ada baris sama
    // sekali (supaya percobaan PERTAMA tetap jalan normal, bukan malah
    // ke-skip keliru).
    protected function alreadySent(): bool
    {
        switch ($this->jenisType) {

            case 'pembayaran':
                $total  = DB::table('pembayaran')->where('kode_company', $this->kodeCompany)->count();
                $unsent = DB::table('pembayaran')->where('kode_company', $this->kodeCompany)->whereNull('sent_at')->count();
                return $total > 0 && $unsent === 0;

            case 'reminder':
                $query = fn () => DB::table('reminder')->where(DB::raw('LEFT(TRIM(kode_company),4)'), $this->kodeCompany);
                return $query()->count() > 0 && $query()->whereNull('sent_at')->count() === 0;

            case 'apo':
                $apoCodes = CompanyGroup::apoCodes($this->kodeCompany);
                $query = fn () => DB::table('apo')->whereIn(DB::raw('LEFT(case_id,3)'), $apoCodes);
                return $query()->count() > 0 && $query()->whereNull('sent_at')->count() === 0;

            // [DITAMBAHKAN] MPR beda dari jenis lain: listing MPR tidak memfilter
            // sent_at, jadi company yang sudah pernah dikirim boleh dikirim ulang.
            // Kalau guard-nya "semua baris sudah sent_at", pengiriman ulang akan
            // ke-skip diam-diam. Makanya di sini dicek: apakah sent_at terisi
            // SETELAH job ini dibuat (artinya percobaan sebelumnya dari job INI
            // sudah berhasil kirim).
            case 'mpr':
                return DB::table('mpr_datas')
                    ->where('kode_company', $this->kodeCompany)
                    ->where('sent_at', '>=', $this->dispatchedAt)
                    ->exists();
        }

        return false;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[BcEmailJob] GAGAL permanen', [
            'jenis'        => $this->jenisType,
            'kode_company' => $this->kodeCompany,
            'to'           => $this->emails,
            'message'      => $exception->getMessage(),
        ]);

        DB::table('email_logs')->insert([
            'kode_company'  => $this->kodeCompany,
            'template_name' => $this->templateName . ' (GAGAL: ' . $exception->getMessage() . ')',
            'subject'       => $this->subject,
            'company_name'  => $this->companyName,
            'recipient'     => implode(', ', $this->emails),
            'total_case'    => $this->totalCase,
            'aging_filter'  => $this->agingFilter,
            'sent_at'       => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}