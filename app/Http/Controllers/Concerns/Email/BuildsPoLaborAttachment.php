<?php

namespace App\Http\Controllers\Concerns\Email;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PoLaborExport;
use Carbon\Carbon;
use App\Support\CompanyGroup;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Data & preview lampiran Excel PO Labor.
 *
 * Dipecah dari EmailController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: normalizePoLaborKeterangan(), isApoJenis(), getPoLaborRowsForExport(), buildAttachmentPreview()
 */
trait BuildsPoLaborAttachment
{
    /**
     * [DITAMBAHKAN] Keterangan untuk Excel PO Labor, disamakan dengan hasil
     * Export di popup Review Part:
     *  - hanya isi dropdown RR + isian manual (tanpa awalan "RR -", tanpa
     *    "Close Repair tanpa part", tanpa label otomatis "Void Case")
     *  - kata "Dapat Labor" tidak ikut
     *  - case berstatus Cancel diberi awalan "Cancel" ("Cancel - teks" / "Cancel")
     * Data lama di tabel apo tetap aman karena dinormalkan saat Excel dibuat.
     */
    private function normalizePoLaborKeterangan($row)
    {
        $k  = trim((string) ($row->keterangan ?? ''));
        $ra = trim((string) ($row->repair_action ?? ''));

        // Kosong / sama dengan Repair Action = hasil fallback, bukan isian user
        if ($k === $ra) {
            $k = '';
        }

        if ($k !== '') {
            $k = preg_replace('/\[\[CAT:[^\]]+\]\]/', '', $k);
            $k = preg_replace('/^RR\s*-\s*/i', '', $k);

            if (preg_match('/^RR$/i', trim($k))) {
                $k = '';
            }

            $k = preg_replace('/^Close Repair tanpa part\s*(-\s*)?/i', '', $k);
            $k = preg_replace('/\bVoid Case\b\s*(-\s*)?/i', '', $k);
            $k = preg_replace('/dapat labor/i', '', $k);
            $k = preg_replace('/^[\s\-–,;:]+|[\s\-–,;:]+$/u', '', $k);
            $k = trim(preg_replace('/\s{2,}/', ' ', $k));
        }

        if (str_contains(strtolower((string) ($row->case_status ?? '')), 'cancel')
            && !preg_match('/cancel/i', $k)) {
            $k = $k !== '' ? 'Cancel - ' . $k : 'Cancel';
        }

        $row->keterangan = $k;

        return $row;
    }

    /**
     * [DITAMBAHKAN] Apakah request/template ini PO Labor (APO)? Kondisinya sama
     * dengan $isapo di send().
     */
    private function isApoJenis(Request $request, $template): bool
    {
        $jenis = strtolower(trim($template->jenis_monitoring ?? ''));

        return str_contains($jenis, 'po labor')
            || str_contains($jenis, 'apo')
            || $request->jenis_upload === 'M004-PO Labor';
    }

    /**
     * [DITAMBAHKAN] Data PO Labor (tabel apo) per company untuk lampiran Excel.
     * Query & pembersihan kolom Keterangan SAMA dengan yang dipakai send(),
     * supaya isi preview = isi Excel yang benar-benar dikirim.
     */
    private function getPoLaborRowsForExport(string $kodeCompany)
    {
        return DB::table('apo')
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
                'keterangan'
            )
            ->whereIn(DB::raw('LEFT(case_id,3)'), CompanyGroup::apoCodes($kodeCompany))
            ->whereNull('sent_at')
            ->get()
            ->map(fn ($row) => $this->normalizePoLaborKeterangan($row));
    }

    /**
     * [DITAMBAHKAN] Blok "Lampiran" untuk halaman preview:
     *  - Untuk PO Labor: file Excel dibuat dengan PoLaborExport yang SAMA seperti
     *    saat kirim, lalu dibaca ulang dan ditampilkan sebagai tabel, jadi yang
     *    terlihat di preview = isi file yang akan terkirim.
     *  - Lampiran manual (upload di form) ditampilkan nama filenya.
     * Tidak mengubah data apa pun (tidak menyimpan file, tidak mengisi sent_at).
     */
    private function buildAttachmentPreview(
        string $kodeCompany,
        string $fallbackCompanyName,
        Request $request,
        $template
    ): string {
        $jenisMonitoring = strtolower(trim($template->jenis_monitoring ?? ''));

        $isapo = str_contains($jenisMonitoring, 'po labor')
            || str_contains($jenisMonitoring, 'apo')
            || $request->jenis_upload === 'M004-PO Labor';

        $boxStyle  = 'margin-top:18px; border:1px solid #dee2e6; border-radius:8px; overflow:hidden;';
        $headStyle = 'background:#f1f5f9; padding:10px 14px; font-size:13px; font-weight:600; color:#0f172a;';
        $noteStyle = 'padding:12px 14px; font-size:13px; color:#475569;';

        $html = '';

        // ---------- Lampiran Excel PO Labor (otomatis) ----------
        if ($isapo) {

            // Nama company untuk nama file: sama seperti send() (email_masters kategori apo1)
            $companyName = $fallbackCompanyName;
            $apoMaster = DB::table('email_masters')
                ->where(DB::raw('LEFT(kode_company,3)'), substr($kodeCompany, 0, 3))
                ->where('kategori', 'apo1')
                ->first();
            if ($apoMaster && !empty($apoMaster->company_name)) {
                $companyName = $apoMaster->company_name;
            }

            $periode = Carbon::now()->subMonth()->locale('id')->translatedFormat('F_Y');
            $safeCompany = preg_replace('/[^A-Za-z0-9_-]/', '_', $companyName);
            $excelName = 'PO_Labor_' . $safeCompany . '_' . $periode . '.xlsx';

            $poLaborRows = $this->getPoLaborRowsForExport($kodeCompany);

            if ($poLaborRows->isEmpty()) {
                $html .= '<div style="' . $boxStyle . '">'
                    . '<div style="' . $headStyle . '">Lampiran Excel</div>'
                    . '<div style="' . $noteStyle . '">Tidak ada data PO Labor untuk company ini, '
                    . 'jadi email akan dikirim <strong>tanpa</strong> lampiran Excel.</div>'
                    . '</div>';
            } else {
                $tmpBase = null;
                $tmpXlsx = null;

                try {
                    $raw = Excel::raw(new PoLaborExport($poLaborRows), \Maatwebsite\Excel\Excel::XLSX);

                    $tmpBase = tempnam(sys_get_temp_dir(), 'bcprev_');
                    $tmpXlsx = $tmpBase . '.xlsx';
                    file_put_contents($tmpXlsx, $raw);

                    $spreadsheet = IOFactory::load($tmpXlsx);

                    $maxRows = 1000;
                    $sheetsHtml = '';
                    $totalRowsAll = 0;

                    foreach ($spreadsheet->getAllSheets() as $sheet) {
                        $data = $sheet->toArray(null, true, true, false);

                        // buang baris yang benar-benar kosong
                        $data = array_values(array_filter($data, function ($r) {
                            foreach ($r as $v) {
                                if ($v !== null && trim((string) $v) !== '') {
                                    return true;
                                }
                            }
                            return false;
                        }));

                        if (empty($data)) {
                            continue;
                        }

                        // Cari baris header asli (yang berisi "Case ID"); baris di atasnya
                        // adalah judul laporan ("ISD Labor Report", "Periode : ...").
                        $headerIdx = 0;
                        foreach ($data as $i => $r) {
                            foreach ($r as $v) {
                                if (is_string($v) && preg_match('/^\s*case id\s*$/i', $v)) {
                                    $headerIdx = $i;
                                    break 2;
                                }
                            }
                        }

                        $titleRows = array_slice($data, 0, $headerIdx);
                        $headerRow = $data[$headerIdx];
                        $dataRows  = array_slice($data, $headerIdx + 1);

                        $totalData = count($dataRows);
                        $totalRowsAll += $totalData;
                        $shown     = array_slice($dataRows, 0, $maxRows);
                        $colCount  = max(1, count($headerRow));

                        // Tinggi area tabel: judul + header + 10 baris data, sisanya di-scroll.
                        $visibleRows = 10;
                        $rowPx       = 22;
                        $boxHeight   = (count($titleRows) + 1 + $visibleRows) * $rowPx + 6;

                        if ($spreadsheet->getSheetCount() > 1) {
                            $sheetsHtml .= '<div style="padding:8px 14px 0; font-size:12px; font-weight:600; color:#475569;">Sheet: '
                                . e($sheet->getTitle()) . '</div>';
                        }

                        $sheetsHtml .= '<div style="overflow:auto; max-height:' . $boxHeight . 'px;">'
                            . '<table border="1" cellpadding="4" cellspacing="0" '
                            . 'style="border-collapse:collapse; font-size:11px; white-space:nowrap; min-width:100%;">';

                        $sheetsHtml .= '<tbody>';

                        // judul laporan (di atas header)
                        foreach ($titleRows as $i => $r) {
                            $text = trim(implode(' ', array_filter(array_map(
                                fn ($v) => is_scalar($v) ? trim((string) $v) : '',
                                $r
                            ), fn ($v) => $v !== '')));

                            $sheetsHtml .= '<tr><td colspan="' . $colCount . '" style="border:none; '
                                . ($i === 0 ? 'font-weight:bold; ' : '') . '">' . e($text) . '</td></tr>';
                        }

                        // header kolom (menempel di atas saat di-scroll)
                        $sheetsHtml .= '<tr>';
                        foreach ($headerRow as $h) {
                            $sheetsHtml .= '<th style="position:sticky; top:0; z-index:1; background:#e9ecef; text-align:left;">'
                                . e((string) $h) . '</th>';
                        }
                        $sheetsHtml .= '</tr>';

                        foreach ($shown as $r) {
                            $sheetsHtml .= '<tr>';
                            foreach ($r as $v) {
                                $sheetsHtml .= '<td>' . e(is_scalar($v) || $v === null ? (string) $v : json_encode($v)) . '</td>';
                            }
                            $sheetsHtml .= '</tr>';
                        }

                        $sheetsHtml .= '</tbody></table></div>';

                        if ($totalData > $maxRows) {
                            $sheetsHtml .= '<div style="' . $noteStyle . '">Menampilkan ' . $maxRows
                                . ' dari ' . $totalData . ' baris (file asli berisi semua baris).</div>';
                        }

                    }

                    $html .= '<div style="' . $boxStyle . '">'
                        . '<div style="' . $headStyle . '">Lampiran Excel : ' . e($excelName)
                        . ' <span style="font-weight:400; color:#64748b;">&middot; ' . $totalRowsAll . ' baris data</span></div>'
                        . ($sheetsHtml !== '' ? $sheetsHtml : '<div style="' . $noteStyle . '">File Excel kosong.</div>')
                        . '</div>';
                } catch (\Throwable $e) {
                    logger()->error('Preview lampiran Excel gagal', [
                        'kode_company' => $kodeCompany,
                        'error' => $e->getMessage(),
                    ]);

                    $html .= '<div style="' . $boxStyle . '">'
                        . '<div style="' . $headStyle . '">Lampiran Excel : ' . e($excelName) . '</div>'
                        . '<div style="' . $noteStyle . ' color:#b91c1c;">Preview isi Excel gagal dibuat: '
                        . e($e->getMessage()) . '</div>'
                        . '</div>';
                } finally {
                    if ($tmpXlsx && file_exists($tmpXlsx)) {
                        @unlink($tmpXlsx);
                    }
                    if ($tmpBase && file_exists($tmpBase)) {
                        @unlink($tmpBase);
                    }
                }
            }
        }

        // ---------- Lampiran manual (upload di form) ----------
        $manualFiles = $request->file('attachments');
        if ($manualFiles) {
            $names = [];
            foreach ((array) $manualFiles as $file) {
                if ($file) {
                    $names[] = '<span style="display:inline-block; margin:0 6px 6px 0; padding:3px 10px; '
                        . 'background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; font-size:12px;">'
                        . e($file->getClientOriginalName()) . '</span>';
                }
            }

            if (!empty($names)) {
                $html .= '<div style="' . $boxStyle . '">'
                    . '<div style="' . $headStyle . '">Lampiran lain (upload manual)</div>'
                    . '<div style="padding:12px 14px 6px;">' . implode('', $names) . '</div>'
                    . '</div>';
            }
        }

        return $html;
    }
}
