<?php

namespace App\Http\Controllers;

use App\Jobs\SendBroadcastEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| BroadcastController
|--------------------------------------------------------------------------
| Fitur "Broadcast All" — beda dengan alur Template Email (yang subject &
| body-nya diambil dari tabel email_templates dan berisi placeholder tabel
| case), di sini Subjek & Body TIDAK disimpan sebagai template — murni
| diisi manual tiap kali mau broadcast, lewat form yang mirip "Edit
| Template" (input teks untuk Subjek, Summernote untuk Body).
|
| Penerima dipilih manual lewat checklist company (mirip halaman BC Email),
| bukan otomatis ke semua company tanpa pilihan.
|
| ★★★ DIUBAH CLAUDE (BARU): pengiriman SEKARANG PAKAI LARAVEL QUEUE.
| Sebelumnya method send() mengirim email SATU-SATU secara sinkron di dalam
| request HTTP yang sama — untuk ratusan/ribuan company ini berisiko bikin
| request timeout (PHP max_execution_time habis di tengah jalan).
|
| Sekarang send() cuma: (1) validasi, (2) siapkan data & attachment,
| (3) dispatch banyak job kecil (SendBroadcastEmailJob) ke queue, lalu
| langsung merespons ke user — TIDAK menunggu semua email selesai terkirim.
| Proses kirim yang sesungguhnya berjalan di background lewat
| `php artisan queue:work`. Lihat catatan setup queue di bagian akhir
| file ini / dokumentasi terpisah. ★★★
|--------------------------------------------------------------------------
*/

class BroadcastController extends Controller
{
    /**
     * Halaman form Broadcast All — tampilkan daftar semua company (untuk info
     * siapa saja yang akan menerima broadcast).
     *
     * ★★★ DIUBAH CLAUDE: sebelumnya query ini ->get() mentah dari email_masters,
     * tapi ternyata tabel itu SATU BARIS PER ALAMAT EMAIL (bukan satu baris per
     * company dengan email digabung koma) — makanya company yang sama muncul
     * berkali-kali dengan email kosong di baris duplikatnya. Sekarang di-GROUP BY
     * kode_company + company_name, semua emailnya digabung jadi satu pakai
     * GROUP_CONCAT (otomatis skip yang kosong/duplikat). ★★★
     */
    public function index()
    {
        $companies = DB::table('email_masters')
            ->select(
                'kode_company',
                'company_name',
                'kategori',
                DB::raw("GROUP_CONCAT(DISTINCT NULLIF(TRIM(email), '') SEPARATOR ', ') as email")
            )
            ->groupBy('kode_company', 'company_name', 'kategori')
            ->orderByRaw('CAST(kode_company AS UNSIGNED) ASC')
            ->get();

        return view('broadcast_all', compact('companies'));
    }

    /**
     * Preview hasil Subjek + Body sebelum dikirim (dipanggil via AJAX,
     * mengembalikan potongan HTML untuk ditampilkan di modal).
     */
    public function preview(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string',
            'body'    => 'nullable|string',
        ]);

        $subject = $request->subject ?: '(Belum ada subjek)';
        $body    = $request->body ?: '<p class="text-muted">(Belum ada isi email)</p>';

        // Body dari Summernote sudah berupa HTML biasa (bukan kode Blade),
        // jadi aman ditampilkan langsung — sama seperti body Template Email.
        return view('broadcast_preview', compact('subject', 'body'));
    }

    /**
     * Kirim broadcast ke company yang ada di email_masters (sesuai filter kategori).
     *
     * ★★★ DIUBAH CLAUDE: sebelumnya kirim berdasarkan checklist ($request->selected_company).
     * Sekarang otomatis ke SEMUA company (tidak perlu pilih satu-satu lagi), pakai query
     * dedup yang sama seperti index(). ★★★
     *
     * ★★★ DIUBAH CLAUDE: ditambahkan dedup GLOBAL per alamat email — sebelumnya kalau
     * satu alamat email terdaftar di lebih dari satu company/kategori (misal ada di
     * company APO, Finance, dan KLA sekaligus), dia menerima broadcast SEKALI PER
     * COMPANY (jadi bisa dobel 2-3x isi sama). Sekarang setiap alamat email hanya
     * dimasukkan ke SATU job saja, siapa pun company pemiliknya. ★★★
     *
     * ★★★ DIUBAH CLAUDE (BARU): method ini TIDAK LAGI mengirim email secara langsung/
     * sinkron. Sekarang cuma menyiapkan data (dedup, attachment) lalu men-DISPATCH
     * job (SendBroadcastEmailJob) ke Laravel Queue — satu job per company. Pengiriman
     * email yang sesungguhnya terjadi belakangan di background, diproses oleh
     * `php artisan queue:work`. Ini menghindari request timeout kalau penerimanya
     * ratusan/ribuan company. ★★★
     */
    public function send(Request $request)
    {
        $request->validate([
            'subject'           => 'required|string|max:255',
            'body'              => 'required|string',
            'attachments.*'     => 'nullable|file|max:20480', // maks 20MB per file
            'kategori'          => 'nullable|array',
            'kategori.*'        => 'in:apo,apo1,finance,kla',
            'selected_company'  => 'nullable|array',
            'selected_company.*' => 'string',
        ]);

        $subject = $request->subject;
        $body    = $request->body;

        // ★★★ BARU: sekarang WAJIB pilih company secara eksplisit lewat checkbox
        // di tabel (name="selected_company[]"), tidak lagi otomatis kirim ke SEMUA
        // company yang cocok kategori. Kategori filter (apo/finance/kla) tetap
        // dipakai sebagai lapis keamanan tambahan di server (jaga-jaga kalau ada
        // yang coba submit kode_company di luar kategori yang sedang difilter). ★★★
        $selectedCompanies = array_filter((array) $request->input('selected_company', []));

        if (empty($selectedCompanies)) {
            return back()->with('error', 'Pilih minimal 1 company penerima dulu sebelum mengirim broadcast.');
        }

        // ★★★ BARU: filter penerima berdasarkan kategori company (apo/finance/kla),
        // bisa pilih lebih dari satu (checkbox). Array kosong / tidak dikirim
        // berarti tidak difilter — kirim ke semua kategori. ★★★
        $kategoriFilter = array_values(array_intersect(
            $request->input('kategori', []),
            ['apo', 'apo1', 'finance', 'kla']
        ));

        $kategoriLabel = empty($kategoriFilter)
            ? ''
            : implode(', ', array_map('ucfirst', $kategoriFilter));

        $companiesQuery = DB::table('email_masters')
            ->select(
                'kode_company',
                'company_name',
                'kategori',
                DB::raw("GROUP_CONCAT(DISTINCT NULLIF(TRIM(email), '') SEPARATOR ', ') as email")
            )
            ->whereIn('kode_company', $selectedCompanies)
            ->groupBy('kode_company', 'company_name', 'kategori');

        if (!empty($kategoriFilter)) {
            $companiesQuery->whereIn('kategori', $kategoriFilter);
        }

        $companies = $companiesQuery->get();

        $user = auth()->user();

        $ccEmails = [];

        if (!empty($user->cc)) {
            // ★★★ DIUBAH CLAUDE: sebelumnya cuma di-split pakai titik-koma (;), tapi
            // ternyata ada data user yang cc-nya dipisah pakai KOMA (,) — hasil split
            // jadi "emailA, emailB" tergabung dalam satu string yang tidak valid
            // sebagai satu alamat email (bikin Symfony menolak dengan
            // RfcComplianceException). Sekarang terima KEDUA pemisah, dan alamat yang
            // ternyata tidak valid otomatis dibuang (bukan bikin seluruh broadcast
            // gagal). ★★★
            $ccEmails = array_values(array_filter(
                array_map('trim', preg_split('/[;,]+/', $user->cc)),
                fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            ));
        }

        // ★★★ BARU: attachment disimpan dulu ke disk (folder unik per broadcast),
        // karena job akan diproses NANTI di background oleh queue worker — file upload
        // asli dari request ini sudah tidak ada lagi begitu request selesai. Folder ini
        // otomatis dihapus setelah SEMUA job selesai diproses (lihat Bus::batch()->finally
        // di bawah). ★★★
        $broadcastId   = (string) Str::uuid();
        $attachmentDir = "broadcast-attachments/{$broadcastId}";
        $attachmentMeta = [];

        if ($request->hasFile('attachments')) {

            foreach ($request->file('attachments') as $file) {

                $storedPath = $file->store($attachmentDir, 'local');

                $attachmentMeta[] = [
                    'path' => $storedPath,
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        // ★★★ Dedup GLOBAL per alamat email — satu alamat email cuma dimasukkan ke
        // SATU job saja, meskipun dia terdaftar di beberapa company/kategori berbeda.
        // Tanpa ini, alamat yang sama bisa menerima broadcast dobel/tiga kali. ★★★
        $globalSentEmails = [];
        $jobs = [];
        $skippedCompanies = [];   // company tanpa email terdaftar sama sekali
        $duplicateSkipped  = [];  // company yang SEMUA emailnya sudah kebagian job lewat company lain
        $plannedRecipientCount = 0;

        foreach ($companies as $emailMaster) {

            if (empty($emailMaster->email)) {
                $skippedCompanies[] = $emailMaster->kode_company;
                continue;
            }

            // ★★★ DIUBAH CLAUDE: tambah validasi format email (FILTER_VALIDATE_EMAIL)
            // supaya kalau ada data email yang typo/rusak di email_masters, alamat itu
            // otomatis dibuang, bukan bikin exception yang menggagalkan seluruh
            // pengiriman ke company tsb. ★★★
            $emails = array_values(array_filter(
                array_map('trim', explode(',', $emailMaster->email)),
                fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            ));

            if (empty($emails)) {
                $skippedCompanies[] = $emailMaster->kode_company;
                continue;
            }

            $newEmails = [];

            foreach ($emails as $email) {

                $emailKey = strtolower($email);

                if (isset($globalSentEmails[$emailKey])) {
                    continue; // sudah masuk job company lain, skip
                }

                $globalSentEmails[$emailKey] = true;
                $newEmails[] = $email;
            }

            if (empty($newEmails)) {
                $duplicateSkipped[] = $emailMaster->company_name . ' (' . $emailMaster->kode_company . ')';
                continue;
            }

            $plannedRecipientCount += count($newEmails);

            $jobs[] = [
                'job'   => new SendBroadcastEmailJob(
                    $emailMaster->kode_company,
                    $emailMaster->company_name,
                    $newEmails,
                    $ccEmails,
                    $subject,
                    $body,
                    $attachmentMeta,
                    $user->email,
                    $user->smtp_password,
                    'PT. Harmoni Putra Solusindo',
                    $kategoriLabel
                ),
                'count' => count($newEmails),
            ];
        }

        if (empty($jobs)) {

            // Tidak ada job yang perlu dikirim — hapus lagi folder attachment yang
            // sudah kadung disimpan tadi, supaya tidak numpuk file percuma.
            Storage::disk('local')->deleteDirectory($attachmentDir);

            $noneMsg = !empty($kategoriFilter)
                ? "Tidak ada email yang bisa dikirim (tidak ada company dengan kategori \"{$kategoriLabel}\" yang punya email terdaftar)."
                : 'Tidak ada email yang bisa dikirim (tidak ada company dengan email terdaftar).';

            return back()->with('error', $noneMsg);
        }

        // ★★★ BARU: pengiriman bertahap — supaya tidak menembak SEMUA email
        // sekaligus dan berpotensi kena limit rate SMTP provider (`mail.harmoniputra.com`
        // biasanya cuma izinkan ratusan email/jam untuk shared hosting). Job dikelompokkan
        // per BATCH_SIZE alamat email; batch pertama langsung jalan, batch berikutnya
        // di-delay kelipatan BATCH_DELAY_MINUTES. Karena ini pakai job->delay() (bukan
        // sleep()), TIDAK memblokir apapun — worker tetap bisa proses broadcast/job lain
        // di waktu yang sama, cuma job2 batch belakangan "ditahan" queue sampai jadwalnya.
        //
        // ★★★ DIUBAH CLAUDE: batch size & jeda MENYESUAIKAN JAM saat broadcast di-klik —
        // >= jam 17:00 (5 sore) = kirim lebih kencang (95 email/jam), < jam 17:00 (pagi
        // sampai sore sebelum jam 5) = kirim lebih pelan/hati-hati (75 email/1,5 jam).
        // Silakan ubah angka-angka di bawah kalau limit provider SMTP Anda beda.
        //
        // ★★★ BARU: KHUSUS role 'apo1' (PO Labor) dan 'finance' (Pembayaran &
        // Invoice Pembayaran) — dipakai aturan TETAP 50 email/batch, jeda 1 jam,
        // MENGGANTIKAN aturan berbasis jam di atas (bukan ditambah). Role lain
        // (apo2, admin, kla, dst) tetap pakai aturan berbasis jam seperti biasa. ★★★
        $currentHour   = now()->hour;
        $userRole      = auth()->user()->role ?? null;
        $isLimitedRole = in_array($userRole, ['apo1', 'finance']);

        if ($isLimitedRole) {
            // Role dengan pembatasan tetap — 50 email/batch, jeda 1 jam,
            // berlaku kapan pun broadcast dikirim (tidak peduli jam berapa)
            $batchSize         = 50;
            $batchDelayMinutes = 60; // 1 jam
        } elseif ($currentHour >= 17) {
            // >= 17:00 (5 sore) — kirim lebih kencang
            $batchSize         = 95;
            $batchDelayMinutes = 60; // 1 jam
        } else {
            // < 17:00 (sebelum jam 5 sore) — kirim lebih pelan/hati-hati
            $batchSize         = 30;
            $batchDelayMinutes = 30; // 1,5 jam
        }

        $cumulativeCount = 0;
        $totalBatches    = 1;

        foreach ($jobs as &$jobData) {

            $batchIndex = intdiv($cumulativeCount, $batchSize);

            if ($batchIndex > 0) {
                $jobData['job']->delay(now()->addMinutes($batchIndex * $batchDelayMinutes));
            }

            $cumulativeCount += $jobData['count'];
            $totalBatches = max($totalBatches, $batchIndex + 1);
        }
        unset($jobData);

        $jobInstances = array_map(fn ($jobData) => $jobData['job'], $jobs);

        // ★★★ BARU: dispatch semua job lewat Bus::batch(). Batch dipilih (bukan
        // dispatch satu-satu biasa) supaya kita bisa pasang ->finally() untuk
        // otomatis menghapus folder attachment sementara SETELAH semua job selesai
        // (baik sukses maupun gagal), tanpa perlu job terpisah khusus buat itu. ★★★
        $totalJobs = count($jobs);

        Bus::batch($jobInstances)
            ->name('Broadcast All - ' . now()->format('Y-m-d H:i:s'))
            ->allowFailures() // satu job gagal tidak menghentikan job2 lain di batch ini
            ->finally(function () use ($attachmentDir) {
                Storage::disk('local')->deleteDirectory($attachmentDir);
            })
            ->dispatch();

        Log::info('[Broadcast] Batch job di-dispatch ke queue', [
            'total_company'  => $totalJobs,
            'total_email'    => $plannedRecipientCount,
            'total_batch'    => $totalBatches,
            'batch_size'     => $batchSize,
            'delay_per_batch_minutes' => $batchDelayMinutes,
            'jam_saat_broadcast' => $currentHour,
            'user_role'      => $userRole,
            'pakai_limit_role_tetap' => $isLimitedRole,
            'kategori'       => $kategoriFilter,
        ]);

        // ★★★ Pesan sekarang bilang "SEDANG DIPROSES", bukan "berhasil terkirim" —
        // karena benar-benar terkirimnya baru terjadi belakangan di background.
        // Status akhir tiap company bisa dicek di halaman Riwayat Email Terkirim
        // begitu job-nya selesai diproses oleh queue worker. ★★★
        $message = "🚀 Broadcast sedang diproses di background ke <strong>{$totalJobs} company</strong> "
            . "(≈{$plannedRecipientCount} alamat email unik)"
            . (!empty($kategoriFilter) ? " untuk kategori: {$kategoriLabel}" : '')
            . '. Proses kirim berjalan di belakang layar (tidak akan bikin halaman ini macet/timeout '
            . 'meskipun penerimanya banyak) — cek statusnya di halaman <strong>Riwayat Email Terkirim</strong> '
            . 'beberapa saat lagi.';

        if ($totalBatches > 1) {

            $estimatedFinishMinutes = ($totalBatches - 1) * $batchDelayMinutes;
            $estimatedFinishHoursLabel = rtrim(rtrim(number_format($estimatedFinishMinutes / 60, 1, ',', ''), '0'), ',');
            $delayLabel = rtrim(rtrim(number_format($batchDelayMinutes / 60, 1, ',', ''), '0'), ',');

            $limitNote = $isLimitedRole
                ? ' (mengikuti batas pengiriman khusus akun Anda)'
                : '';

            $message .= "<br><small>📦 Dikirim bertahap: <strong>{$totalBatches} batch</strong> "
                . "(±{$batchSize} email/batch, jeda {$delayLabel} jam antar batch{$limitNote}) — "
                . "batch terakhir diperkirakan mulai sekitar {$estimatedFinishHoursLabel} jam dari sekarang.</small>";
        }

        $detailDisplayLimit = 15;

        if (!empty($skippedCompanies)) {

            $shown = array_slice($skippedCompanies, 0, $detailDisplayLimit);
            $message .= '<br><small class="text-danger"><strong>Dilewati (tidak ada email terdaftar):</strong> ' . implode('; ', $shown);

            $remaining = count($skippedCompanies) - count($shown);
            if ($remaining > 0) {
                $message .= "; dan {$remaining} lainnya";
            }

            $message .= '</small>';
        }

        if (!empty($duplicateSkipped)) {

            $shown = array_slice($duplicateSkipped, 0, $detailDisplayLimit);
            $message .= '<br><small class="text-muted"><strong>Dilewati (email sudah masuk antrean lewat company lain, dihindari dobel):</strong> ' . implode('; ', $shown);

            $remaining = count($duplicateSkipped) - count($shown);
            if ($remaining > 0) {
                $message .= "; dan {$remaining} lainnya";
            }

            $message .= '</small>';
        }

        return back()->with('success', $message);
    }
}