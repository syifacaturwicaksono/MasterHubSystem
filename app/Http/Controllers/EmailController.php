<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplate;

// [DITAMBAHKAN] untuk fitur lampiran Excel PO Labor
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PoLaborExport;
use Carbon\Carbon;
use App\Support\CompanyGroup; // [DITAMBAHKAN] kode gabungan PO Labor (mis. ELS COMPUTER = 630)

// [DITAMBAHKAN] untuk fitur batching queue (Pembayaran/Reminder/PO Labor)
use App\Jobs\SendBcEmailJob;
use App\Support\BroadcastScheduler;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\Email\FormatsReminderText;
use App\Http\Controllers\Concerns\Email\RendersCaseTables;
use App\Http\Controllers\Concerns\Email\BuildsPoLaborAttachment;
use App\Http\Controllers\Concerns\Email\HandlesEmailPreview;

class EmailController extends Controller
{
    use FormatsReminderText,
        RendersCaseTables,
        BuildsPoLaborAttachment,
        HandlesEmailPreview;


    /**
     * Render satu tabel case menjadi HTML murni (bukan Blade), aman dipakai
     * bersama body yang berasal dari WYSIWYG (Summernote).
     *
     * @param mixed  $rows    Collection data (hasil query)
     * @param array  $columns ['nama_field_db' => 'Label Kolom', ...] — urutan sesuai urutan tampil
     * @param array  $options
     *   - aging_operator  : '>' atau '>=' (default: tidak ada highlight kalau null)
     *   - aging_threshold : angka pembanding aging
     *   - date_fields     : ['nama_field' => 'format_carbon'] contoh ['received_date' => 'd-M-Y']
     *   - empty_text      : teks saat data kosong (kalau null, tabel tidak ditampilkan sama sekali)
     */
    // [DITAMBAHKAN] ===== Reminder Invoice: isi otomatis dari Excel =====
    private const BULAN_ID = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

    public function send(Request $request)
    {
  
        $selectedCompanies = $request->selected_company ?? [];
        $template = EmailTemplate::find($request->template_id);

        if (!$template) {
            return back()->with('error', 'Template belum dipilih');
        }

        // [DITAMBAHKAN] Sama seperti preview(): Pembayaran cocokkan email_masters
        // pakai 3 digit pertama kode_company.
        $ispembayaranEarly = str_contains(strtolower(trim($template->jenis_monitoring ?? '')), 'pembayaran');

        // [DITAMBAHKAN] Jenis yang WAJIB dikirim bertahap (batch + jeda), sesuai role
        // apo1 (PO Labor) dan finance (Pembayaran & Reminder Invoice). Jenis lain
        // (WIP, Pending, KCI, Finish Repair, MPR, KLA) TETAP kirim langsung seperti
        // biasa, tidak berubah.
        $jenisMonitoringForBatch = strtolower(trim($template->jenis_monitoring ?? ''));

        // [DITAMBAHKAN] MPR ikut dikirim bertahap, memakai timer yang SAMA dengan
        // Pembayaran/Reminder/PO Labor (batch_size & delay_minutes di Edit Timer).
        // Catatan: komentar di atas yang menyebut MPR "kirim langsung" sudah tidak berlaku.
        $isMprBatch = str_contains($jenisMonitoringForBatch, 'mpr')
            || $request->jenis_upload === 'M001-MPR';

        $isBatchedJenis = $ispembayaranEarly
            || str_contains($jenisMonitoringForBatch, 'reminder')
            || str_contains($jenisMonitoringForBatch, 'invoice')
            || str_contains($jenisMonitoringForBatch, 'po labor')
            || $request->jenis_upload === 'M004-PO Labor'
            || $isMprBatch;

        // [DITAMBAHKAN] Penampung job untuk jenis yang di-batch, dan folder
        // attachment sementara (file upload asli hilang begitu request selesai,
        // job baru diproses belakangan di background).
        $bcJobs = [];
        $bcAttachmentDir = 'bc-email-attachments/' . (string) Str::uuid();

        $footer = DB::table('footer_masters')->where('id', $request->footer_id)->first();

        foreach ($selectedCompanies as $kodeCompany) {
    $emailMastersQuery = DB::table('email_masters');

    // 1. Tentukan kondisi filter berdasarkan jenis_upload / template
    if ($ispembayaranEarly) {
        $emailMastersQuery->where(
            DB::raw('LEFT(kode_company,4)'),
            substr($kodeCompany, 0, 4)
            
        );
    } elseif ($request->jenis_upload === 'M004-PO Labor') {
        $emailMastersQuery->where(
            DB::raw('LEFT(kode_company,3)'),
            substr($kodeCompany, 0, 3)
        )->where('kategori', 'apo1'); // Hanya filter kategori 'apo1'
    } else {
        $emailMastersQuery->where('kode_company', $kodeCompany);
    }

    // 2. Eksekusi query CUKUP SATU KALI saja di sini
    $emailMasters = $emailMastersQuery->get();

    // 3. Cek apakah data kosong (CUKUP SATU KALI)
    if ($emailMasters->isEmpty()) {
        continue;
    }

    // 4. Proses pemisahan string email
    $emails = $emailMasters
        ->pluck('email')
        ->flatMap(function ($email) {
            $email = str_replace(';', ',', $email);
            return array_map('trim', explode(',', $email));
        })
        ->filter(function ($email) {
            return !empty($email);
        })
        ->unique()
        ->values()
        ->toArray();

    if (empty($emails)) {
        continue;
    }

    $companyName = $emailMasters->first()->company_name;

    // [DITAMBAHKAN] PO Labor: kode gabungan (ELS COMPUTER) pakai nama grup
    // supaya Subject & nama file Excel tidak memuat nama satu cabang saja.
    if ($this->isApoJenis($request, $template) && ($groupName = CompanyGroup::groupName($kodeCompany))) {
        $companyName = $groupName;
    }

 
            // 1. Ambil Data WIP
            $query = DB::table('wip_datas')
                ->where('case_id_manual', 'like', $kodeCompany . '%')
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $query->where('aging', $match[1], (float) $match[2]);
                }
            }

            $rows = $query->orderByDesc('aging')->get()->map(function ($row) {
                $row->aging = number_format($row->aging, 1);
                return $row;
            });

            // 2. Ambil Data Pending Case 5D
            $pendingQuery = DB::table('pending')
                ->select(
                    'case_id',
                    'company_name',
                    'received_date',
                    'start_repair_date',
                    'case_status',
                    'ce_name',
                    'company_city',
                    'part_name',
                    'aging',
                    'hp_part_no',
                    'part_request_date',
                    'so_no',
                    'eta_date',
                    'part_in_date',
                    'product_no',
                    'product_name'
                )
                ->where(DB::raw('LEFT(case_id,3)'), $kodeCompany)
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $pendingQuery->where('aging', $match[1], (float) $match[2]);
                }
            }

            // 3. Pending 14D
            $pending14dQuery = DB::table('pending_14d')
                ->select(
                    'company_name',
                    'aging',
                    'case_id',
                    'received_date',
                    'part_request_date',
                    'eta_date',
                    'part_in_date',
                    'so_no',
                    'hp_part_no',
                    'case_status',
                    'ce_name'
                )
                ->where(DB::raw('LEFT(case_id,3)'), $kodeCompany)
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $pending14dQuery->where('aging', $match[1], (float) $match[2]);
                }
            }

            $pendingCase14dRows = $pending14dQuery
                ->orderByDesc('aging')
                ->get()
                ->map(function ($row) {
                    $row->aging = number_format($row->aging, 1);
                    return $row;
                });

            // 4. KCI
            $kciQuery = DB::table('kci')
                ->select(
                    'case_id',
                    'count',
                    'company_name',
                    'aging',
                    'customer_name',
                    'case_status',
                    'ce_name',
                    'company_city'
                )
                ->where(DB::raw('LEFT(case_id,3)'), $kodeCompany)
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $kciQuery->where('aging', $match[1], (float) $match[2]);
                }
            }

            $kciRows = $kciQuery->orderByDesc('aging')->get();

            // 5. Finish Repair
            $finishrepairQuery = DB::table('finish_repair')
                ->select(
                    'case_id',
                    'count',
                    'company_name',
                    'aging',
                    'customer_name',
                    'case_status',
                    'ce_name',
                    'company_city'
                )
                ->where(DB::raw('LEFT(case_id,3)'), $kodeCompany)
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $finishrepairQuery->where('aging', $match[1], (float) $match[2]);
                }
            }

            $finishrepairRows = $finishrepairQuery->orderByDesc('aging')->get();

            // 6. [DITAMBAHKAN] Query Reminder Invoice
            $reminderQuery = DB::table('reminder')
                ->select(
                    'kode_company',
                    'periode_closing',
                    'partner',
                    'case',
                    'total',
                    'kekurangan_dokumen',
                    'jatuh_tempo'
                )
                ->where(DB::raw('LEFT(TRIM(kode_company),4)'), $kodeCompany)
                ->whereNull('sent_at');

            $reminderRows = $reminderQuery->orderByDesc('kode_company')->get()->map(function ($row) {
                if (isset($row->total) && $row->total !== '' && $row->total !== null) {
                    $val = (float) $row->total;

                    // Jika bilangan bulat, tampilkan tanpa desimal. Jika ada desimal, ambil sesuai nilai desimalnya.
                    if (fmod($val, 1) == 0) {
                        $formattedNumber = number_format($val, 0, ',', '.');
                    } else {
                        // Hilangkan angka 0 yang tidak berguna di paling belakang desimal
                        $formattedNumber = rtrim(rtrim(number_format($val, 2, ',', '.'), '0'), ',');
                    }

                    $row->total = 'Rp ' . $formattedNumber;
                } else {
                    $row->total = '-';
                }

                return $row;
            });

            // Pemisahan Aging Pending
            $pendingAbove14Rows = $pendingCase14dRows;

            $pendingCase5dRows = $pendingQuery
                ->orderByDesc('aging')
                ->get()
                ->map(function ($row) {
                    $row->aging = number_format($row->aging, 1);
                    return $row;
                });

            $pendingBelow5Rows = $pendingCase5dRows->filter(function ($row) {
                return (float) $row->aging < 5;
            });

            $pendingAbove5Rows = $pendingCase5dRows->filter(function ($row) {
                return (float) $row->aging >= 5;
            });

            // 7. Query MPR
            $mprRow = DB::table('mpr_datas')
                ->where('kode_company', $kodeCompany)
                ->first();

            // 8. [DITAMBAHKAN] Query Pembayaran
            $pembayaranRows = DB::table('pembayaran')
                ->where('kode_company', $kodeCompany)
                ->whereNull('sent_at')
                ->get();

            // [DITAMBAHKAN] Khusus Pembayaran: pakai nama dari data Excel Pembayaran itu
            // sendiri (bukan dari email_masters yang kadang ada nama kotanya), biar
            // Subject & Header email konsisten sama isi tabel di body.
            if ($ispembayaranEarly && $pembayaranRows->isNotEmpty()) {
                $companyName = $this->findCommonCompanyName(
                    $pembayaranRows->pluck('company_name')->unique()->values()->all()
                );
            }

            // 9. [DITAMBAHKAN] Query KLA
            $klaRows = DB::table('kla')
                ->select('company_name', 'case_id', 'upload', 'transaksi', 'keterangan', 'timestamp')
                ->where(DB::raw('LEFT(TRIM(case_id),1)'), $kodeCompany)
                ->whereNull('sent_at')
                ->get();


            // SUBJECT DINAMIS
            $subject = str_replace(
                ['{{tanggal_kirim}}', '{{company_name}}'],
                [
                    now()->format('d/m/Y'),
                    $companyName
                ],
                $template->subject
            );

            // [DITAMBAHKAN] {{subject}} diisi dari kolom "Subject" di Excel Pembayaran
            // (mis. "APRIL 2026"), bukan bulan berjalan.
            $subject = $this->fillSubjectPlaceholder(
                $subject,
                $this->pembayaranSubjectText($pembayaranRows)
            );
            $subject = trim(preg_replace('/\s{2,}/', ' ', $subject));

            $body = $template->body;

            // Deteksi jenis monitoring
            $jenisMonitoring = strtolower(trim($template->jenis_monitoring ?? ''));

            $isWip = str_contains($jenisMonitoring, 'wip');
            $isPending5d = str_contains($jenisMonitoring, '5day') || str_contains($jenisMonitoring, 'tat5');
            $isPending14d = str_contains($jenisMonitoring, '14day') || str_contains($jenisMonitoring, 'tat14');
            $iskci = str_contains($jenisMonitoring, 'kci');
            $isfinishrepair = str_contains($jenisMonitoring, 'finish repair') || str_contains($jenisMonitoring, 'finishrepair');

            // Reminder
            $isreminder = str_contains($jenisMonitoring, 'reminder')
                || str_contains($jenisMonitoring, 'invoice');

            // MPR
            $ismpr = str_contains($jenisMonitoring, 'mpr');

            // Pembayaran
            $ispembayaran = str_contains($jenisMonitoring, 'pembayaran');

            // KLA
            $iskla = str_contains($jenisMonitoring, 'kla');

            // PO Labor / APO
            $isapo = str_contains($jenisMonitoring, 'po labor')
                || str_contains($jenisMonitoring, 'apo')
                || $request->jenis_upload === 'M004-PO Labor';

            // Query PO Labor / APO
            $poLaborRows = collect();

            if ($isapo) {
                $poLaborRows = DB::table('apo')
                    ->select(
                        'case_id',
                        'case_id_manual',
                        'company_name',
                        'received_date',
                        'warranty_status',
                        'serial_no',
                        'product_tower',
                        'product_no',
                        'product_name',
                        'case_type',
                        'case_status',
                        'problem_desc',
                        'repair_action',
                        'customer_company',
                        'customer_name',
                        'pic_name',
                        'city',
                        'phone_no',
                        'mobile_no',
                        'start_repair_date',
                        'tat_case',
                        'finish_date',
                        'closed_date',
                        'labor',
                        'keterangan',
                    )
                    // [DIUBAH] whereIn: kode gabungan (ELS COMPUTER = 630) mengambil
                    // semua case cabangnya (630, 633, dst) dalam satu Excel.
                    ->whereIn(DB::raw('LEFT(case_id,3)'), CompanyGroup::apoCodes($kodeCompany))
                    ->whereNull('sent_at')
                    ->get()
                    // [DITAMBAHKAN] Kolom "Keterangan" di Excel PO Labor hanya berisi
                    // keterangan dari popup review (RR, Close Repair tanpa part,
                    // Intermittent Case, dst). Data lama yang isinya sama persis dengan
                    // Repair Action (hasil fallback lama) dikosongkan supaya tidak dobel.
                    // [DIUBAH] pakai aturan yang sama dengan Export di popup Review Part
                    // (lihat normalizePoLaborKeterangan di atas).
                    ->map(fn ($row) => $this->normalizePoLaborKeterangan($row));
            }

            // Placeholder tanggal & nama company (bisa dipakai di body template mana saja)
            $body = str_replace(
                ['{{tanggal_kirim}}', '{{company_name}}'],
                [strtoupper(now()->locale('id')->translatedFormat('F Y')), $mprRow->company_name ?? $companyName],
                $body
            );

            // [DITAMBAHKAN] {{subject}} di body juga diisi dari kolom "Subject" Excel Pembayaran
            $body = $this->fillSubjectPlaceholder(
                $body,
                $this->pembayaranSubjectText($pembayaranRows)
            );

            // [DITAMBAHKAN] Reminder Invoice: Periode Closing & Jatuh Tempo otomatis dari Excel
            if ($isreminder) {
                $body = $this->applyReminderPlaceholders($body, $reminderRows);
                $subject = $this->applyReminderPlaceholders($subject, $reminderRows);
            }

            $footerHtml = nl2br($footer->footer_html ?? '');

            // [DITAMBAHKAN] Sertakan $isreminder & $reminderRows ke method helper
            $tablePlaceholders = $this->buildCaseTablePlaceholders(
                $isWip,
                $isPending5d,
                $isPending14d,
                $iskci,
                $isfinishrepair,
                $isreminder,        // <-- Parameter tambahan
                $ismpr,
                $rows,
                $pendingBelow5Rows,
                $pendingAbove5Rows,
                $pendingAbove14Rows,
                $kciRows,
                $finishrepairRows,
                $reminderRows,      // <-- Parameter tambahan
                $mprRow,
                $ispembayaran,      // [DITAMBAHKAN]
                $pembayaranRows,    // [DITAMBAHKAN]
                $iskla,             // [DITAMBAHKAN]
                $klaRows            // [DITAMBAHKAN]
            );

            $footerHtmlStyled = '<div style="font-family: Arial, sans-serif; font-size:12px; color:#000; line-height:1.5;">'
                . $footerHtml
                . '</div>';

            $html = $this->injectCaseTables($body, $tablePlaceholders) . $footerHtmlStyled;

            $ccEmails = [];
            if (!empty(auth()->user()->cc)) {
                $ccEmails = array_map('trim', explode(';', auth()->user()->cc));
            }

            $bccEmails = [];
            $attachments = $request->file('attachments');
            $user = auth()->user();

            config([
                'mail.mailers.smtp.host' => 'mail.harmoniputra.com',
                'mail.mailers.smtp.port' => 465,
                'mail.mailers.smtp.encryption' => 'ssl',
                'mail.mailers.smtp.username' => $user->email,
                'mail.mailers.smtp.password' => $user->smtp_password,
                'mail.from.address' => $user->email,
                'mail.from.name' => 'PT. Harmoni Putra Solusindo',
            ]);

            Mail::purge('smtp');
            $excelPath = null;
            $excelName = null;

            if ($isapo && $poLaborRows->isNotEmpty()) {

                // Bulan sebelumnya dari bulan kirim
                $periode = Carbon::now()
                    ->subMonth()
                    ->locale('id')
                    ->translatedFormat('F_Y');

                $safeCompany = preg_replace('/[^A-Za-z0-9_-]/', '_', $companyName);

                $excelName = 'PO_Labor_' . $safeCompany . '_' . $periode . '.xlsx';

                // [DIUBAH] Kalau jenis ini di-batch (dikirim belakangan lewat queue),
                // simpan ke folder khusus batch supaya bisa dibersihkan bareng semua
                // attachment lain setelah SEMUA job selesai (lihat ->finally() di bawah).
                // Kalau tidak di-batch (kirim langsung seperti biasa), simpan ke
                // 'temp/' seperti sebelumnya (tidak berubah).
                if ($isBatchedJenis) {
                    Excel::store(
                        new PoLaborExport($poLaborRows),
                        $bcAttachmentDir . '/' . $excelName
                    );
                    $excelPath = storage_path('app/' . $bcAttachmentDir . '/' . $excelName);
                } else {
                    Excel::store(
                        new PoLaborExport($poLaborRows),
                        'temp/' . $excelName
                    );
                    $excelPath = storage_path('app/temp/' . $excelName);
                }

                logger()->info('Excel dibuat', [
                    'path' => $excelPath,
                    'exists' => file_exists($excelPath),
                ]);
            }

            // [DITAMBAHKAN] Hitung total case untuk Email Logs — dipindah ke sini
            // (sebelumnya di bawah) supaya bisa dipakai baik jalur batch (buat data
            // job) maupun jalur kirim langsung (tidak berubah logic-nya).
            $totalCase = 0;

            if ($isWip) {
                $totalCase = $rows->count();
            } elseif ($isPending5d) {
                $totalCase = $pendingCase5dRows->count();
            } elseif ($isPending14d) {
                $totalCase = $pendingCase14dRows->count();
            } elseif ($iskci) {
                $totalCase = $kciRows->count();
            } elseif ($isfinishrepair) {
                $totalCase = $finishrepairRows->count();
            } elseif ($isreminder) {
                $totalCase = $reminderRows->count();
            } elseif ($ismpr) {
                $rawTotal = $mprRow->total_case ?? 0;

                if (is_numeric($rawTotal)) {
                    $totalCase = (int) round((float) $rawTotal);
                } else {
                    $cleanTotal = str_replace('.', '', (string) $rawTotal);
                    $cleanTotal = str_replace(',', '.', $cleanTotal);
                    $totalCase = (int) round((float) $cleanTotal);
                }
            } elseif ($ispembayaran) {
                $totalCase = $pembayaranRows->count();
            } elseif ($iskla) {
                $totalCase = $klaRows->count();
            } elseif ($isapo) {
                $totalCase = $poLaborRows->count();
            }

            // [DITAMBAHKAN] ============ JALUR BATCH (Pembayaran/Reminder/PO Labor) ============
            // Jangan kirim sekarang, jangan update sent_at, jangan insert email_logs di
            // sini — semuanya dikerjakan oleh SendBcEmailJob SETELAH job-nya benar-benar
            // diproses queue (supaya company yang masih di batch belakangan TIDAK
            // ke-mark "sent_at" duluan sebelum email-nya beneran terkirim).
            if ($isBatchedJenis) {

                $bcAttachmentMeta = [];

                if ($attachments) {
                    foreach ($attachments as $file) {
                        $storedPath = $file->store($bcAttachmentDir, 'local');
                        $bcAttachmentMeta[] = [
                            'path' => $storedPath,
                            'name' => $file->getClientOriginalName(),
                        ];
                    }
                }

                if ($excelPath && file_exists($excelPath)) {
                    $bcAttachmentMeta[] = [
                        'path' => $bcAttachmentDir . '/' . $excelName,
                        'name' => $excelName,
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ];
                }

                // [DIUBAH] tambah 'mpr' — kalau tidak, job MPR akan berlabel 'apo'
                // dan salah menandai tabel apo sebagai terkirim.
                $jenisTypeForJob = $ispembayaran
                    ? 'pembayaran'
                    : ($isreminder ? 'reminder' : ($isMprBatch ? 'mpr' : 'apo'));

                $bcJobs[] = [
                    'job' => new SendBcEmailJob(
                        $kodeCompany,
                        $companyName,
                        $emails,
                        $ccEmails,
                        $bccEmails,
                        $subject,
                        $html,
                        $bcAttachmentMeta,
                        $user->email,
                        $user->smtp_password,
                        'PT. Harmoni Putra Solusindo',
                        $template->nama_template,
                        $totalCase,
                        $request->aging_filter,
                        $jenisTypeForJob
                    ),
                    'count' => count($emails),
                ];

                // Lanjut ke company berikutnya — skip kirim langsung + update sent_at +
                // insert email_logs di bawah (semua itu jadi tanggung jawab job).
                continue;
            }
            // [DITAMBAHKAN] ============ AKHIR JALUR BATCH ============

            Mail::html($html, function ($message) use ($emails, $ccEmails, $subject, $attachments, $bccEmails, $excelPath, $excelName) {

                $message
                    ->from(auth()->user()->email, 'PT. Harmoni Putra Solusindo')
                    ->to($emails)
                    ->cc($ccEmails)
                    ->bcc($bccEmails)
                    ->subject($subject);

                // Attachment manual dari form
                if ($attachments) {
                    foreach ($attachments as $file) {
                        $message->attach(
                            $file->getRealPath(),
                            ['as' => $file->getClientOriginalName()]
                        );
                    }
                }

                // Attachment Excel otomatis dari database
                if ($excelPath && file_exists($excelPath)) {
                    $message->attach($excelPath, [
                        'as' => $excelName,
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }
            });
            // Hapus file excel sementara
            if ($excelPath && file_exists($excelPath)) {
                @unlink($excelPath);
            }

            // UPDATE UPDATE DATA TERKIRIM (`sent_at`)

            // 1. UPDATE WIP
            $updateWipQuery = DB::table('wip_datas')
                ->where('case_id_manual', 'like', $kodeCompany . '%')
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $updateWipQuery->where('aging', $match[1], (float) $match[2]);
                }
            }
            $updateWipQuery->update(['sent_at' => now()]);

            // 2. UPDATE PENDING
            $updatePendingQuery = DB::table('pending')
                ->where('case_id', 'like', $kodeCompany . '%')
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $updatePendingQuery->where('aging', $match[1], (float) $match[2]);
                }
            }
            $updatePendingQuery->update(['sent_at' => now()]);

            // 3. UPDATE PENDING 14D
            $updatePending14Query = DB::table('pending_14d')
                ->where('case_id', 'like', $kodeCompany . '%')
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $updatePending14Query->where('aging', $match[1], (float) $match[2]);
                }
            }
            $updatePending14Query->update(['sent_at' => now()]);

            // 4. UPDATE KCI
            $updatekciQuery = DB::table('kci')
                ->where('case_id', 'like', $kodeCompany . '%')
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $updatekciQuery->where('aging', $match[1], (float) $match[2]);
                }
            }
            $updatekciQuery->update(['sent_at' => now()]);

            // 5. UPDATE Finish Repair
            $updatefinishrepairQuery = DB::table('finish_repair')
                ->where('case_id', 'like', $kodeCompany . '%')
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $updatefinishrepairQuery->where('aging', $match[1], (float) $match[2]);
                }
            }
            $updatefinishrepairQuery->update(['sent_at' => now()]);

            // 6. [DITAMBAHKAN] UPDATE Reminder Invoice
            DB::table('reminder')
                ->where(DB::raw('LEFT(TRIM(kode_company),4)'), $kodeCompany)
                ->whereNull('sent_at')
                ->update(['sent_at' => now()]);

            // 7. UPDATE MPR
            DB::table('mpr_datas')
                ->where('kode_company', $kodeCompany)
                ->whereNull('sent_at')
                ->update(['sent_at' => now()]);

            // 8. [DITAMBAHKAN] UPDATE Pembayaran
            DB::table('pembayaran')
                ->where('kode_company', $kodeCompany)
                ->whereNull('sent_at')
                ->update(['sent_at' => now()]);

            // 9. [DITAMBAHKAN] UPDATE KLA
            DB::table('kla')
                ->where(DB::raw('LEFT(TRIM(case_id),1)'), $kodeCompany)
                ->whereNull('sent_at')
                ->update(['sent_at' => now()]);

            // 10. [DITAMBAHKAN] UPDATE PO Labor (apo) — sebelumnya kelewatan,
            // jadi baris PO Labor tidak pernah ditandai terkirim dan terus
            // menumpuk di "Statistik per Jenis Monitoring".
            // [DIUBAH] Kode gabungan (ELS COMPUTER) hanya berlaku untuk PO Labor;
            // jenis lain tetap memakai awalan 3 digit seperti semula.
            DB::table('apo')
                ->whereIn(
                    DB::raw('LEFT(case_id,3)'),
                    $isapo
                        ? CompanyGroup::apoCodes($kodeCompany)
                        : [substr($kodeCompany, 0, 3)]
                )
                ->whereNull('sent_at')
                ->update(['sent_at' => now()]);

           
            // 4. Simpan log email
            DB::table('email_logs')->insert([
                'kode_company' => $kodeCompany,
                'template_name' => $template->nama_template,
                'subject' => $subject,
                'company_name' => $companyName,
                'recipient' => implode(', ', $emails),
                'total_case' => $totalCase,
                'aging_filter' => $request->aging_filter,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // [DITAMBAHKAN] ============ DISPATCH BATCH (Pembayaran/Reminder/PO Labor) ============
        // Setelah loop foreach selesai (semua company sudah diproses — baik yang
        // kirim langsung maupun yang masuk antrean job), kalau ada job yang perlu
        // di-batch, jadwalkan & dispatch semuanya sekarang lewat Bus::batch().
        if (!empty($bcJobs)) {

            $settings          = BroadcastScheduler::getSettings();
            $batchSize         = $settings['batch_size'];
            $batchDelayMinutes = $settings['delay_minutes'];

            // Hitung dulu berapa total batch yang dibutuhkan (buat reservasi jadwal global)
            $cumulativeCount = 0;
            $totalBatches    = 1;

            foreach ($bcJobs as $jobData) {
                $batchIndex = intdiv($cumulativeCount, $batchSize);
                $cumulativeCount += $jobData['count'];
                $totalBatches = max($totalBatches, $batchIndex + 1);
            }

            // Reservasi slot jadwal GLOBAL — supaya kalau ada pengiriman lain (Broadcast
            // All / BC Email jenis lain) yang jadwalnya masih berjalan, batch ini otomatis
            // mulai SETELAH itu longgar, bukan bareng-bareng di jam yang sama.
            $startAt = BroadcastScheduler::reserveSlot($totalBatches, $batchDelayMinutes);

            $cumulativeCount = 0;

            foreach ($bcJobs as &$jobData) {

                $batchIndex = intdiv($cumulativeCount, $batchSize);

                // Selalu set delay eksplisit (bukan cuma kalau batchIndex > 0), karena
                // $startAt sendiri bisa saja sudah di masa depan (masih ngantre di
                // belakang pengiriman lain yang jadwalnya belum longgar).
                $jobData['job']->delay(
                    $startAt->copy()->addMinutes($batchIndex * $batchDelayMinutes)
                );

                $cumulativeCount += $jobData['count'];
            }
            unset($jobData);

            $jobInstances = array_map(fn ($jobData) => $jobData['job'], $bcJobs);
            $totalJobs    = count($bcJobs);

            Bus::batch($jobInstances)
                ->name('BC Email - ' . $template->nama_template . ' - ' . now()->format('Y-m-d H:i:s'))
                ->allowFailures()
                ->finally(function () use ($bcAttachmentDir) {
                    Storage::disk('local')->deleteDirectory($bcAttachmentDir);
                })
                ->dispatch();

            $message = "🚀 Email sedang diproses di background ke <strong>{$totalJobs} company</strong>. "
                . 'Proses kirim berjalan di belakang layar — cek statusnya di halaman '
                . '<strong>Riwayat Email Terkirim</strong> beberapa saat lagi.';

            if ($totalBatches > 1) {
                $message .= "<br><small>📦 Dikirim bertahap: <strong>{$totalBatches} batch</strong> "
                    . "(±{$batchSize} email/batch, jeda {$batchDelayMinutes} menit antar batch).</small>";
            }

            if ($startAt->isFuture()) {
                $message .= '<br><small>⏳ Ada antrean pengiriman lain yang masih berjalan — batch pertama '
                    . 'pengiriman ini dijadwalkan mulai sekitar <strong>' . $startAt->format('H:i') . '</strong>.</small>';
            }

            return back()->with('success', $message);
        }
        // [DITAMBAHKAN] ============ AKHIR DISPATCH BATCH ============

        return back()->with('success', 'Email berhasil dikirim');
    }

    // =====================================================================
    // [DITAMBAHKAN] {{subject}} untuk email Pembayaran
    // Isinya diambil dari kolom "Subject" di Excel Pembayaran (mis. "APRIL 2026"),
    // bukan bulan berjalan. Kalau 1 company punya beberapa Subject berbeda
    // (mis. APRIL 2026 dan MEI 2026) digabung dengan " & ".
    // Dipakai juga oleh preview (trait HandlesEmailPreview).
    // =====================================================================
    protected function pembayaranSubjectText($pembayaranRows): string
    {
        if (!$pembayaranRows) {
            return '';
        }

        return collect($pembayaranRows)
            ->pluck('subject')
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->unique()
            ->implode(' & ');
    }

    protected function fillSubjectPlaceholder($text, string $subjectText): string
    {
        return (string) preg_replace(
            '/\{\{\s*subject\s*\}\}/i',
            str_replace(['\\', '$'], ['\\\\', '\\$'], $subjectText),
            (string) $text
        );
    }
}