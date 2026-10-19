<?php

namespace App\Http\Controllers\Concerns\Email;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\ImportController;

/**
 * Render tabel case (HTML) dan penyisipan placeholder tabel ke body email.
 *
 * Dipecah dari EmailController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: renderCaseTable(), renderMprTable(), renderPembayaranTable(), findCommonCompanyName(), buildCaseTablePlaceholders(), injectCaseTables()
 */
trait RendersCaseTables
{
    private function renderCaseTable($rows, array $columns, array $options = []): string
    {
        $agingOperator = $options['aging_operator'] ?? null;
        $agingThreshold = $options['aging_threshold'] ?? null;
        $dateFields = $options['date_fields'] ?? [];
        $emptyText = $options['empty_text'] ?? null;

        if (!$rows || $rows->isEmpty()) {
            if ($emptyText === null) {
                return '';
            }
        }

        $html = '<table border="1" cellpadding="4" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:12px;">';
        $html .= '<thead><tr style="background-color:#d9d9d9; text-align:center;"><th>No</th>';

        foreach ($columns as $label) {
            $html .= '<th>' . e($label) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        if (!$rows || $rows->isEmpty()) {
            $html .= '<tr><td colspan="' . (count($columns) + 1) . '" align="center">'
                . e($emptyText) . '</td></tr>';
        } else {
            $no = 1;
            foreach ($rows as $row) {
                $html .= '<tr>';
                $html .= '<td align="center">' . $no++ . '</td>';

                foreach (array_keys($columns) as $field) {
                    $value = $row->{$field} ?? '';

                    // Format tanggal via Carbon
                    if (isset($dateFields[$field])) {
                        $value = $value
                            ? \Carbon\Carbon::parse($value)->format($dateFields[$field])
                            : '-';
                    }

                    // Highlight kolom aging sesuai ambang batas
                    if ($field === 'aging' && $agingOperator !== null && $agingThreshold !== null) {
                        $isHighlight = $agingOperator === '>='
                            ? ((float) $value >= $agingThreshold)
                            : ((float) $value > $agingThreshold);

                        $bg = $isHighlight ? '#ffb3b3' : 'white';

                        $html .= '<td style="background-color:' . $bg . '; text-align:center; font-weight:bold;">'
                            . e($value) . '</td>';
                    } elseif ($field === 'aging') {
                        $html .= '<td style="text-align:center; font-weight:bold;">' . e($value) . '</td>';
                    } else {
                        $html .= '<td>' . e($value) . '</td>';
                    }
                }

                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Render tabel MPR (Monthly Performance Review) menjadi HTML murni,
     * termasuk highlight merah kalau melewati target dan box Target/Catatan.
     */
    private function renderMprTable($mprRow): string
    {
        if (!$mprRow) {
            return '<p><em>Data MPR tidak ditemukan untuk company ini.</em></p>';
        }

        $tatValue = (float) str_replace(',', '.', $mprRow->tat ?? '0');
        $tatWoBoValue = (float) str_replace(',', '.', $mprRow->tat_wo_bo ?? '0');
        $olaValue = (float) str_replace(',', '.', $mprRow->ola ?? '0');
        $rrpPercentValue = (float) str_replace(['%', ','], ['', '.'], $mprRow->rrp_percent ?? '0');
        $wipValue = (float) str_replace(',', '.', $mprRow->wip ?? '0');
        $latestAgingValue = (float) str_replace(',', '.', $mprRow->latest_aging ?? '0');

        $redStyle = 'background:#ff6666;color:#fff;font-weight:bold;';

        $tatStyle = $tatValue >= 6.5 ? $redStyle : '';
        $tatWoBoStyle = $tatWoBoValue >= 6.5 ? $redStyle : '';
        $olaStyle = $olaValue >= 3.5 ? $redStyle : '';
        $rrpPercentStyle = $rrpPercentValue > 5 ? $redStyle : '';
        $wipStyle = $wipValue >= 14.5 ? $redStyle : '';
        $agingStyle = $latestAgingValue >= 14.5 ? $redStyle : '';

        $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:collapse;text-align:center;">';
        $html .= '<tr style="font-weight:bold;">';
        $html .= '<td colspan="4" style="border:none;"></td>';
        $html .= '<td colspan="8" style="background:#FCE5A5;">Target</td>';
        $html .= '</tr>';
        $html .= '<tr style="font-weight:bold;">';
        $html .= '<td colspan="4" style="border:none;"></td>';
        $html .= '<td colspan="2" style="background:#FCE5A5;">&lt;6 Day</td>';
        $html .= '<td style="background:#FCE5A5;">&lt;3 Day</td>';
        $html .= '<td style="background:#FCE5A5;">&nbsp;</td>';
        $html .= '<td style="background:#FCE5A5;">&lt;5%</td>';
        $html .= '<td style="background:#FCE5A5;">&lt;14 Day</td>';
        $html .= '<td style="background:#FCE5A5;">0</td>';
        $html .= '<td style="background:#FCE5A5;">&lt;14 Day</td>';
        $html .= '</tr>';
        $html .= '<tr style="background:#D9E2F3;font-weight:bold;">';
        $html .= '<td>COMPANY NAME</td><td>CASE IPG</td><td>CASE PSG</td><td>TOTAL CASE</td>';
        $html .= '<td>TAT</td><td>TAT w/o BO</td><td>OLA</td><td>RRP</td><td>RRP %</td>';
        $html .= '<td>WIP</td><td>UNUPDATE WIP</td><td>LATEST AGING</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td>' . e($mprRow->company_name) . '</td>';
        $html .= '<td>' . e($mprRow->case_ipg) . '</td>';
        $html .= '<td>' . e($mprRow->case_psg) . '</td>';
        $html .= '<td>' . e($mprRow->total_case) . '</td>';
        $html .= '<td style="' . $tatStyle . '">' . e($mprRow->tat) . '</td>';
        $html .= '<td style="' . $tatWoBoStyle . '">' . e($mprRow->tat_wo_bo) . '</td>';
        $html .= '<td style="' . $olaStyle . '">' . e($mprRow->ola) . '</td>';
        $html .= '<td>' . e($mprRow->rrp) . '</td>';
        $html .= '<td style="' . $rrpPercentStyle . '">' . e($mprRow->rrp_percent) . '</td>';
        $html .= '<td style="' . $wipStyle . '">' . e($mprRow->wip) . '</td>';
        $html .= '<td style="' . $agingStyle . '">' . e($mprRow->unupdate_wip) . '</td>';
        $html .= '<td style="' . $agingStyle . '">' . e($mprRow->latest_aging) . '</td>';
        $html .= '</tr>';
        $html .= '</table><br><br>';

        $html .= '<table width="100%"><tr>';
        $html .= '<td width="45%" valign="top">';
        $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;"><tr><td>';
        $html .= '<b>Target MPR</b><br><br>';
        $html .= '1. TAT : 6 Business Days<br>';
        $html .= '2. OLA : 3 Business Days<br>';
        $html .= '3. RRP 30 Days : 5%<br>';
        $html .= '4. WIP : 1-14 Days';
        $html .= '</td></tr></table>';
        $html .= '</td>';
        $html .= '<td width="10%"></td>';
        $html .= '<td width="45%" valign="top">';
        $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;"><tr><td>';
        $html .= nl2br($mprRow->catatan ?? '');
        $html .= '</td></tr></table>';
        $html .= '</td>';
        $html .= '</tr></table>';

        return $html;
    }

    /**
     * Render tabel Pembayaran Labor Partner sebagai SATU tabel (1 header di
     * paling atas, semua company jadi baris data di dalamnya). Catatan
     * "bukti pembayaran" cuma ditampilkan SEKALI di paling bawah tabel
     * (diambil dari baris yang isinya tidak kosong), bukan diulang tiap baris.
     */
    private function renderPembayaranTable($rows): string
    {
        if (!$rows || $rows->isEmpty()) {
            return '<p><em>Tidak ada data pembayaran ditemukan.</em></p>';
        }

        $cellStyle = 'word-wrap:break-word; overflow-wrap:break-word;';

        $html = '<table border="1" cellpadding="4" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:12px; table-layout:fixed;">';

        $html .= '<thead><tr style="background-color:#ffff00; text-align:center;">';
        $html .= '<th width="4%">NO</th>';
        $html .= '<th width="10%">PERIODE</th>'; // [DITAMBAHKAN] dari kolom Subject Excel
        $html .= '<th width="16%">Company name</th>';
        $html .= '<th width="12%">NO REK</th>';
        $html .= '<th width="7%">BANK</th>';
        $html .= '<th width="15%">A/N</th>';
        $html .= '<th width="18%">KETERANGAN</th>';
        $html .= '<th width="9%">JUMLAH Rp.</th>';
        $html .= '<th width="9%">TOTAL</th>';
        $html .= '</tr></thead>';

        $html .= '<tbody>';

        $no = 1;
        $buktiPembayaran = '';

        foreach ($rows as $row) {

            $html .= '<tr>';
            $html .= '<td align="center" style="' . $cellStyle . '">' . $no++ . '</td>';
            $html .= '<td align="center" style="' . $cellStyle . '">' . e(trim((string) ($row->subject ?? '')) ?: '-') . '</td>'; // [DITAMBAHKAN]
            $html .= '<td style="' . $cellStyle . '">' . e($row->company_name ?? '') . '</td>';
            $html .= '<td style="' . $cellStyle . '">' . e($row->no_rek ?? '') . '</td>';
            $html .= '<td style="' . $cellStyle . '">' . e($row->bank ?? '') . '</td>';
            $html .= '<td style="' . $cellStyle . '">' . e($row->atas_nama ?? '') . '</td>';
            $html .= '<td style="' . $cellStyle . '">' . e($row->keterangan ?? '') . '</td>';
            $html .= '<td align="right" style="' . $cellStyle . '">' . number_format((float) ($row->jumlah ?? 0), 0, ',', '.') . '</td>';
            $html .= '<td align="right" style="' . $cellStyle . '">' . number_format((float) ($row->total ?? 0), 0, ',', '.') . '</td>';
            $html .= '</tr>';

            // Ambil 1 teks bukti pembayaran saja (baris pertama yang isinya tidak kosong)
            if ($buktiPembayaran === '' && !empty(trim($row->bukti_pembayaran ?? ''))) {
                $buktiPembayaran = $row->bukti_pembayaran;
            }
        }

        // Baris catatan bukti pembayaran: cuma 1x, di paling bawah tabel, colspan penuh
        if ($buktiPembayaran !== '') {
            $html .= '<tr>';
            $html .= '<td colspan="9" style="background:#f5f5f5; font-size:12px;">';
            $html .= nl2br(e($buktiPembayaran));
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Bangun semua tabel case yang mungkin dipakai, dikembalikan sebagai array
     * dengan key placeholder.
     */
    /**
     * Cari bagian nama yang sama dari sekumpulan nama company (mis. cabang
     * berbeda kota tapi kode_company sama). Sama persis logic-nya dengan yang
     * dipakai di ImportController::bulk() untuk listing Pembayaran, supaya nama
     * yang tampil di Subject/Header email konsisten dengan yang di listing BC Email.
     */
    private function findCommonCompanyName(array $names): string
    {
        $names = array_values(array_unique($names));

        if (count($names) <= 1) {
            return $names[0] ?? '';
        }

        $wordLists = array_map(fn($n) => preg_split('/\s+/', trim($n)), $names);
        $shortest  = min(array_map('count', $wordLists));

        $commonWords = [];

        for ($i = 0; $i < $shortest; $i++) {
            $word = $wordLists[0][$i];

            foreach ($wordLists as $words) {
                if (strcasecmp($words[$i], $word) !== 0) {
                    break 2;
                }
            }

            $commonWords[] = $word;
        }

        return $commonWords ? implode(' ', $commonWords) : $names[0];
    }

    private function buildCaseTablePlaceholders(
        bool $isWip,
        bool $isPending5d,
        bool $isPending14d,
        bool $iskci,
        bool $isfinishrepair,
        bool $isreminder,
        bool $ismpr,
        $rows,
        $pendingBelow5Rows,
        $pendingAbove5Rows,
        $pendingAbove14Rows,
        $kciRows,
        $finishrepairRows,
        $reminderRows,
        $mprRow = null,
        bool $ispembayaran = false,
        $pembayaranRows = null,
        bool $iskla = false,      // [DITAMBAHKAN]
        $klaRows = null          // [DITAMBAHKAN]
    ): array {

        $placeholders = [];

        if ($isWip) {
            $placeholders['[[TABEL_CASE]]'] = $this->renderCaseTable(
                $rows,
                [
                    'case_id_manual' => 'Case ID',
                    'aging' => 'Aging',
                    'company_name' => 'Company Name',
                    'finish_date' => 'Finish Date',
                    'case_status' => 'Case Status',
                    'hp_part_no' => 'HP Part No',
                    'so_no' => 'SO No',
                    'awb_no_part_return' => 'AWB No Part Return',
                    'part_in_date' => 'Part In Date',
                    'created_at' => 'Today',
                ],
                [
                    'aging_operator' => '>',
                    'aging_threshold' => 14,
                    'empty_text' => 'Tidak ada data ditemukan',
                ]
            );
        }

        if ($isPending5d) {
            $columns5d = [
                'case_id' => 'Case ID',
                'received_date' => 'Received Date',
                'start_repair_date' => 'Start Repair Date',
                'company_name' => 'Company Name',
                'aging' => 'Aging',
                'case_status' => 'Case Status',
                'ce_name' => 'CE Name',
                'company_city' => 'Company City',
                'part_name' => 'Part Name',
                'hp_part_no' => 'HP Part No',
                'part_request_date' => 'Part Request Date',
                'so_no' => 'SO No',
                'eta_date' => 'ETA Date',
                'part_in_date' => 'Part In Date',
                'product_no' => 'Product No',
                'product_name' => 'Product Name',
            ];

            $placeholders['[[TABEL_PENDING_BELOW_5]]'] = $this->renderCaseTable(
                $pendingBelow5Rows,
                $columns5d,
                [
                    'date_fields' => ['part_request_date' => 'Y-m-d'],
                ]
            );

            $placeholders['[[TABEL_PENDING_ABOVE_5]]'] = $this->renderCaseTable(
                $pendingAbove5Rows,
                $columns5d,
                [
                    'aging_operator' => '>',
                    'aging_threshold' => 5,
                    'date_fields' => ['part_request_date' => 'Y-m-d'],
                ]
            );
        }

        if ($isPending14d) {
            $placeholders['[[TABEL_CASE]]'] = $this->renderCaseTable(
                $pendingAbove14Rows,
                [
                    'company_name' => 'Company Name',
                    'aging' => 'Aging',
                    'case_id' => 'Case ID',
                    'received_date' => 'Received Date',
                    'part_request_date' => 'Part Request Date',
                    'eta_date' => 'ETA Date',
                    'part_in_date' => 'Part In Date',
                    'so_no' => 'SO No',
                    'hp_part_no' => 'HP Part No.',
                    'case_status' => 'Case Status',
                    'ce_name' => 'CE Name',
                ],
                [
                    'aging_operator' => '>=',
                    'aging_threshold' => 14,
                    'date_fields' => [
                        'received_date' => 'd-M-Y',
                        'part_request_date' => 'd-M-Y',
                        'eta_date' => 'd-M-Y',
                        'part_in_date' => 'd-M-Y',
                    ],
                    'empty_text' => 'Tidak ada Pending Case di atas 14 hari.',
                ]
            );
        }

        if ($iskci) {
            $placeholders['[[TABEL_CASE]]'] = $this->renderCaseTable(
                $kciRows,
                [
                    'case_id' => 'Case ID',
                    'company_name' => 'Company Name',
                    'aging' => 'Aging',
                    'customer_name' => 'Customer name',
                    'case_status' => 'Case Status',
                    'ce_name' => 'CE name',
                    'company_city' => 'Company city',
                ],
                [
                    'aging_operator' => '>',
                    'aging_threshold' => 20,
                    'empty_text' => 'Tidak ada data ditemukan',
                ]
            );
        }

        if ($isfinishrepair) {
            $placeholders['[[TABEL_CASE]]'] = $this->renderCaseTable(
                $finishrepairRows,
                [
                    'case_id' => 'Case ID',
                    'count' => 'Count',
                    'company_name' => 'Company Name',
                    'aging' => 'Aging',
                    'customer_name' => 'Customer name',
                    'case_status' => 'Case Status',
                    'ce_name' => 'CE name',
                    'company_city' => 'Company city',
                ],
                [
                    'aging_operator' => '>',
                    'aging_threshold' => 20,
                    'empty_text' => 'Tidak ada data ditemukan',
                ]
            );
        }

        if ($isreminder) {
            $placeholders['[[TABEL_CASE]]'] = $this->renderCaseTable(
                $reminderRows,
                [
                    'periode_closing' => 'Periode Closing',
                    'partner' => 'Partner',
                    'case' => 'Case',
                    'total' => 'Total',
                    'kekurangan_dokumen' => 'Kekurangan Dokumen',
                    'jatuh_tempo' => 'Jatuh Tempo',

                ],
                [
                    'empty_text' => 'Tidak ada data reminder ditemukan',
                ]
            );
        }

        if ($ismpr) {
            $placeholders['[[TABEL_MPR]]'] = $this->renderMprTable($mprRow);
        }

        if ($ispembayaran) {
            $placeholders['[[TABEL_PEMBAYARAN]]'] = $this->renderPembayaranTable($pembayaranRows);
        }

        // [DITAMBAHKAN] KLA
        if ($iskla) {
            // Ubah nilai kosong menjadi 'Belum ada'
            $klaRows = $klaRows->map(function ($row) {
                $row->upload = !empty($row->upload)
                    ? $row->upload
                    : 'Belum ada';

                $row->transaksi = !empty($row->transaksi)
                    ? $row->transaksi
                    : 'Belum ada';

                return $row;
            });

            $placeholders['[[TABEL_CASE]]'] = $this->renderCaseTable(
                $klaRows,
                [
                    'case_id' => 'COF ID',
                    'company_name' => 'Cabang',
                    'upload' => 'Upload File COF',
                    'transaksi' => 'Foto Transaksi',
                    'keterangan' => 'Keterangan',
                    'timestamp' => 'Tanggal Upload',
                ],
                [
                    'empty_text' => 'Tidak ada data KLA ditemukan',
                ]
            );
        }


        return $placeholders;
    }

    /**
     * Sisipkan tabel-tabel case ke posisi placeholder di dalam body.
     */
    private function injectCaseTables(string $body, array $placeholders): string
    {
        $html = $body;
        $anyReplaced = false;

        foreach ($placeholders as $tag => $tableHtml) {
            if (str_contains($html, $tag)) {
                $html = str_replace($tag, $tableHtml, $html);
                $anyReplaced = true;
            }
        }

        if (!$anyReplaced) {
            // Fallback jika body belum pakai placeholder sama sekali
            foreach ($placeholders as $tableHtml) {
                $html .= $tableHtml;
            }
        }

        return $html;
    }
}