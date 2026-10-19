<?php

namespace App\Http\Controllers\Concerns\Email;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EmailTemplate;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\CompanyGroup;

/**
 * Halaman/endpoint preview email sebelum dikirim.
 *
 * Dipecah dari EmailController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: preview()
 */
trait HandlesEmailPreview
{
    public function preview(Request $request)
    {
        $selectedCompanies = $request->selected_company ?? [];
        $template = EmailTemplate::find($request->template_id);

        if (!$template) {
            return 'Template belum dipilih';
        }

        // [DITAMBAHKAN] Deteksi lebih awal: Pembayaran pakai matching 3 digit pertama
        // kode_company (mirip WIP dkk), karena kode di tabel pembayaran (4 digit,
        // mis. 7190) tidak selalu sama persis dengan kode di email_masters (ada yg 3 digit).
        $ispembayaranEarly = str_contains(strtolower(trim($template->jenis_monitoring ?? '')), 'pembayaran');

        $allPreview = '';

        foreach ($selectedCompanies as $kodeCompany) {
            // 1. Query WIP
            $query = DB::table('wip_datas')
                ->select(
                    'case_id_manual',
                    'company_name',
                    'finish_date',
                    'case_status',
                    'hp_part_no',
                    'so_no',
                    'awb_no_part_return',
                    'part_in_date',
                    'aging',
                    'created_at'
                )
                ->where(DB::raw('LEFT(case_id_manual,3)'), $kodeCompany)
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

            // 2. Query Pending 5D
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
                    'product_name',
                    'product_no'
                )
                ->where(DB::raw('LEFT(case_id,3)'), $kodeCompany)
                ->whereNull('sent_at');

            if ($request->filled('aging_filter')) {
                $filter = trim($request->aging_filter);
                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$/', $filter, $match)) {
                    $pendingQuery->where('aging', $match[1], (float) $match[2]);
                }
            }

            $pendingCase5dRows = $pendingQuery->orderByDesc('aging')->get()->map(function ($row) {
                $row->aging = number_format($row->aging, 1);
                return $row;
            });

            // 3. Query Pending 14D
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

            $pendingCase14dRows = $pending14dQuery->orderByDesc('aging')->get()->map(function ($row) {
                $row->aging = number_format($row->aging, 1);
                return $row;
            });

            // 4. Query KCI
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

            // 5. Query Finish Repair
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

            // 6. Query Reminder
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
                ->where(DB::raw('LEFT(TRIM(kode_company), 4)'), $kodeCompany)
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
            $emailMasterQuery = DB::table('email_masters');
            if ($ispembayaranEarly) {
                $emailMasterQuery->where(DB::raw('LEFT(kode_company,3)'), substr($kodeCompany, 0, 3));
            } else {
                $emailMasterQuery->where('kode_company', $kodeCompany);
            }
            $emailMaster = $emailMasterQuery->first();

            $companyName = $emailMaster->company_name ?? $kodeCompany;

            // [DITAMBAHKAN] PO Labor: kode gabungan (ELS COMPUTER) pakai nama grup
            if ($this->isApoJenis($request, $template) && ($groupName = CompanyGroup::groupName($kodeCompany))) {
                $companyName = $groupName;
            }

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

            // DATA PO LABOR
            $poLaborRows = DB::table('apo')
                ->select('case_id', 'product_name', 'case_status', 'labor')
                ->where(DB::raw('LEFT(case_id,3)'), $kodeCompany)
                ->get();

            $pendingBelow5Rows = $pendingCase5dRows->filter(fn($row) => (float) $row->aging < 5);
            $pendingAbove5Rows = $pendingCase5dRows->filter(fn($row) => (float) $row->aging >= 5);
            $pendingAbove14Rows = $pendingCase14dRows;

            $footer = DB::table('footer_masters')->where('id', $request->footer_id)->first();
            $body = $template->body;
            $footerHtml = nl2br($footer->footer_html ?? '');

            // Deteksi jenis monitoring
            $jenisMonitoring = strtolower(trim($template->jenis_monitoring ?? ''));

            $isWip = str_contains($jenisMonitoring, 'wip');
            $isPending5d = str_contains($jenisMonitoring, '5day') || str_contains($jenisMonitoring, 'tat5');
            $isPending14d = str_contains($jenisMonitoring, '14day') || str_contains($jenisMonitoring, 'tat14');
            $iskci = str_contains($jenisMonitoring, 'kci');
            $isfinishrepair = str_contains($jenisMonitoring, 'finish repair') || str_contains($jenisMonitoring, 'finishrepair');
            $isreminder = str_contains($jenisMonitoring, 'reminder') || str_contains($jenisMonitoring, 'invoice');
            $ismpr = str_contains($jenisMonitoring, 'mpr');
            $ispembayaran = str_contains($jenisMonitoring, 'pembayaran'); // [DITAMBAHKAN]
            $iskla = str_contains($jenisMonitoring, 'kla'); // [DITAMBAHKAN]

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
            }

            $tablePlaceholders = $this->buildCaseTablePlaceholders(
                $isWip,
                $isPending5d,
                $isPending14d,
                $iskci,
                $isfinishrepair,
                $isreminder,
                $ismpr,
                $rows,
                $pendingBelow5Rows,
                $pendingAbove5Rows,
                $pendingAbove14Rows,
                $kciRows,
                $finishrepairRows,
                $reminderRows,
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

            // [DITAMBAHKAN] Lampiran (Excel PO Labor + file manual) untuk dikoreksi sebelum kirim
            $attachmentHtml = $this->buildAttachmentPreview(
                (string) $kodeCompany,
                (string) $companyName,
                $request,
                $template
            );

            // Hitung ringkasan total (MPR sengaja ditiadakan dari box ringkasan)
            $totalArray = [];

            if ($isWip)
                $totalArray[] = 'Total Case WIP : ' . $rows->count();
            if ($isPending5d)
                $totalArray[] = 'Total Pending 5D : ' . $pendingCase5dRows->count();
            if ($isPending14d)
                $totalArray[] = 'Total Pending 14D : ' . $pendingCase14dRows->count();
            if ($iskci)
                $totalArray[] = 'Total Case KCI : ' . $kciRows->count();
            if ($isfinishrepair)
                $totalArray[] = 'Total Case Finish Repair : ' . $finishrepairRows->count();
            if ($isreminder)
                $totalArray[] = 'Total Invoice Reminder : ' . $reminderRows->count();
            if ($ispembayaran)
                $totalArray[] = 'Total Pembayaran : ' . $pembayaranRows->count(); // [DITAMBAHKAN]
            if ($iskla)
                $totalArray[] = 'Total Case KLA : ' . $klaRows->count(); // [DITAMBAHKAN]

            $totalText = implode(' | ', $totalArray);

            // Buat HTML box ringkasan hanya jika $totalText ada isinya (non-MPR)
            $totalBoxHtml = '';
            if (!empty($totalText)) {
                $totalBoxHtml = '
                    <div style="background:#f8f9fa; border-left:4px solid #0d6efd; padding:10px 15px; border-radius:6px; margin-bottom:10px;">
                        <strong>' . $totalText . '</strong>
                    </div>';
            }

            // [DITAMBAHKAN] Ganti placeholder subject juga di preview, biar sama seperti pas dikirim beneran
            $subjectPreview = str_replace(
                ['{{tanggal_kirim}}', '{{company_name}}'],
                [strtoupper(now()->locale('id')->translatedFormat('F Y')), $mprRow->company_name ?? $companyName],
                $template->subject
            );

            // [DITAMBAHKAN] {{subject}} diisi dari kolom "Subject" di Excel Pembayaran
            // (mis. "APRIL 2026"), bukan bulan berjalan.
            $subjectPreview = $this->fillSubjectPlaceholder(
                $subjectPreview,
                $this->pembayaranSubjectText($pembayaranRows)
            );
            $subjectPreview = trim(preg_replace('/\s{2,}/', ' ', $subjectPreview));
            if ($isreminder) {
                $subjectPreview = $this->applyReminderPlaceholders($subjectPreview, $reminderRows);
            }


            $allPreview .= '
            <div style="margin-bottom:25px; background:#ffffff; border:1px solid #dee2e6; border-radius:12px; overflow:hidden;">
                <div style="background:#0d6efd; color:#ffffff; padding:15px 20px;">
                    <div style="font-size:22px; font-weight:600; line-height:1.3;">
                        ' . e($companyName) . '
                    </div>
                    <div style="font-size:13px; opacity:0.9; margin-top:3px;">
                        Company Code : ' . e($kodeCompany) . '
                    </div>
                </div>

                <div style="padding:15px;">
                    ' . $totalBoxHtml . '

                    <div style="margin-bottom:10px; font-size:14px;">
                        <strong>Subject :</strong>
                        <span style="color:#495057;">
                            ' . e($subjectPreview) . '
                        </span>
                    </div>

                    <div style="margin:0; padding:0;">
                        ' . $html . '
                    </div>

                    ' . $attachmentHtml . '
                </div>
            </div>';
        }

        return response($allPreview);
    }
}