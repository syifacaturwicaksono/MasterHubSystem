<?php

namespace App\Http\Controllers\Concerns\Import;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Riwayat RR (Repeat Repair) lintas bulan: cek, simpan, scan, preview & konfirmasi.
 *
 * Dipecah dari ImportController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: findRrHistoryMatches(), saveRrHistory(), rrHistoryCheck(), rrHistoryUpload(), scanRrRepeatCaseIds(), rrPreview(), rrConfirm()
 */
trait HandlesRrHistory
{
    // Cari case lama di apo_history untuk tiap case di $fileRows.
    // Return: [case_id => [ {case lama}, ... ]] (hanya yang cocok aturan RR_WINDOW_DAYS).
    private function findRrHistoryMatches(array $fileRows): array
    {
        if (empty($fileRows)) {
            return [];
        }

        $fileSet = array_flip(array_column($fileRows, 'case_id'));
        $serials = array_values(array_unique(array_column($fileRows, 'serial_no')));

        $hist = [];

        foreach (array_chunk($serials, 500) as $chunk) {
            DB::table('apo_history')
                ->whereIn('serial_no', $chunk)
                ->whereNotNull('closed_date')
                ->get()
                ->each(function ($h) use (&$hist, $fileSet) {
                    // case yang sama dengan isi file ini bukan "riwayat"
                    if (!isset($fileSet[$h->case_id])) {
                        $hist[$h->serial_no][] = $h;
                    }
                });
        }

        $matches = [];

        foreach ($fileRows as $r) {

            if (empty($r['received_date']) || empty($hist[$r['serial_no']])) {
                continue;
            }

            foreach ($hist[$r['serial_no']] as $h) {

                $closed = \Carbon\Carbon::parse($h->closed_date)->startOfDay();

                $days = (int) round(
                    ($r['received_date']->timestamp - $closed->timestamp) / 86400
                );

                if ($days >= 0 && $days <= self::RR_WINDOW_DAYS) {
                    $matches[$r['case_id']][] = [
                        'case_id'         => (string) $h->case_id,
                        'company_name'    => (string) $h->company_name,
                        'product_tower'   => (string) $h->product_tower,
                        'product_name'    => (string) $h->product_name,
                        'case_status'     => (string) $h->case_status,
                        'warranty_status' => (string) $h->warranty_status,
                        'repair_action'   => (string) $h->repair_action,
                        'closed_date'     => (string) $h->closed_date,
                        'days'            => $days, // selisih hari: Received baru - Closed lama
                        'history'         => true,
                    ];
                }
            }

            if (!empty($matches[$r['case_id']])) {
                usort($matches[$r['case_id']], fn ($a, $b) => strcmp($a['closed_date'], $b['closed_date']));
            }
        }

        return $matches;
    }

    // Simpan/perbarui semua case di $fileRows ke apo_history (kunci: case_id).
    private function saveRrHistory(array $fileRows, string $kodeUpload): int
    {
        $now = now();

        foreach (array_chunk($fileRows, 200) as $chunk) {

            $payload = array_map(fn ($r) => [
                'case_id'         => $r['case_id'],
                'serial_no'       => $r['serial_no'],
                'company_name'    => $r['company_name'],
                'received_date'   => $r['received_date']?->toDateString(),
                'closed_date'     => $r['closed_date']?->toDateString(),
                'product_tower'   => $r['product_tower'],
                'product_name'    => $r['product_name'],
                'case_status'     => $r['case_status'],
                'warranty_status' => $r['warranty_status'],
                'repair_action'   => $r['repair_action'],
                'kode_upload'     => $kodeUpload,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], $chunk);

            DB::table('apo_history')->upsert(
                $payload,
                ['case_id'],
                [
                    'serial_no', 'company_name', 'received_date', 'closed_date',
                    'product_tower', 'product_name', 'case_status', 'warranty_status',
                    'repair_action', 'kode_upload', 'updated_at',
                ]
            );
        }

        return count($fileRows);
    }

    // POST /upload/rr-history : file yang baru dipilih -> case lama yang cocok (JSON)
    public function rrHistoryCheck(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $rows = $this->readTransactionRows($request->file('file')->getRealPath());

        return response()->json([
            'matches' => $this->findRrHistoryMatches($rows),
        ]);
    }

    // POST /upload/history : isi riwayat saja (mis. file 3 bulan) — TIDAK masuk apo / BC
    public function rrHistoryUpload(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $rows = $this->readTransactionRows($request->file('file')->getRealPath());

        $total = $this->saveRrHistory($rows, 'HIST-' . now()->format('YmdHis'));

        return response()->json([
            'total'   => $total,
            'message' => "Riwayat tersimpan: {$total} case (tidak masuk BC Email).",
        ]);
    }

    // =========================================================
    // [DITAMBAHKAN] 🔥 SCAN RR (RE REPAIR) DARI FILE YANG SAMA
    // -----------------------------------------------------------------
    // Baca sheet "Transaction Report" langsung (Case ID index 1, Serial
    // No index 6, heading row ke-4 - sama seperti ApoImport::headingRow()
    // dan logic client-side di import_blade.php). Case pertama per
    // Serial No TIDAK masuk hasil ini (dia tetap dapat Labor normal);
    // case ke-2/ke-3/dst per Serial No yang sama BARU masuk sini.
    // Return: [case_id => true, ...]
    // =========================================================
    private function scanRrRepeatCaseIds($file): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
            $file->getRealPath()
        );

        $sheet = $spreadsheet->getSheetByName('Transaction Report')
            ?? $spreadsheet->getSheet(0);

        $rows = $sheet->toArray(null, true, true, false);

        // Baris 1-4 = heading (heading row ke-4), data mulai baris ke-5
        $dataRows = array_slice($rows, 4);

        $bySerial = [];

        foreach ($dataRows as $row) {

            $caseId = trim((string) ($row[1] ?? ''));
            $serialNo = trim((string) ($row[6] ?? ''));

            if ($caseId === '' || $serialNo === '') {
                continue;
            }

            if (!isset($bySerial[$serialNo])) {
                $bySerial[$serialNo] = [];
            }

            if (!in_array($caseId, $bySerial[$serialNo], true)) {
                $bySerial[$serialNo][] = $caseId;
            }
        }

        $repeatCaseIds = [];

        foreach ($bySerial as $caseIdList) {

            if (count($caseIdList) > 1) {

                // index 0 = case pertama/original -> SKIP
                foreach (array_slice($caseIdList, 1) as $caseId) {
                    $repeatCaseIds[$caseId] = true;
                }
            }
        }

        // [DITAMBAHKAN] RR lintas bulan: case yang punya case lama (Serial No sama)
        // dengan Received - Closed lama <= RR_WINDOW_DAYS (30 hari) di apo_history juga RR.
        try {
            $historyRows = $this->readTransactionRows($file->getRealPath());

            foreach ($this->findRrHistoryMatches($historyRows) as $caseId => $matched) {
                $repeatCaseIds[$caseId] = true;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Cek RR riwayat gagal', ['error' => $e->getMessage()]);
        }

        return $repeatCaseIds;
    }

    // =========================================================
    // [DITAMBAHKAN] 🔥 RR (RE REPAIR) - PREVIEW
    // -----------------------------------------------------------------
    // Dipanggil dari tombol "Next" di popup Review Part & Kategori.
    // Belum commit ke DB — cuma stash file sementara + override, lalu
    // deteksi RR (Serial No sama, Case ID beda, dalam 1 file/1 bulan
    // yang sama) dari sheet "Transaction Report", dan tampilkan
    // halaman RR untuk direview user.
    // =========================================================
    public function rrPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'jenis_upload' => 'required',
            'kode_upload' => 'required'
        ]);

        $kodeUpload = $request->kode_upload;

        // ------------------------------------------------------
        // STASH FILE SEMENTARA
        // Dipakai lagi nanti oleh rrConfirm() untuk commit sungguhan.
        // ------------------------------------------------------
        $tempPath = $request->file('file')->storeAs(
            'temp-po-labor',
            $kodeUpload . '.xlsx'
        );

        // ------------------------------------------------------
        // STASH OVERRIDE + METADATA KE SESSION
        // ------------------------------------------------------
        session([
            "rr_upload.{$kodeUpload}" => [
                'jenis_upload'  => $request->jenis_upload,
                'kode_upload'   => $kodeUpload,
                'temp_path'     => $tempPath,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'apo_case_overrides' => $request->filled('apo_case_overrides')
                    ? (json_decode($request->apo_case_overrides, true) ?: [])
                    : [],
                'apo_keterangan_overrides' => $request->filled('apo_keterangan_overrides')
                    ? (json_decode($request->apo_keterangan_overrides, true) ?: [])
                    : [],
            ],
        ]);

        // ------------------------------------------------------
        // DETEKSI RR DARI SHEET "Transaction Report"
        // Rule sementara: dalam 1 file, Serial No sama + Case ID beda.
        // Case ID = kolom index 1, Serial No = kolom index 6 (0-based),
        // heading row di baris ke-4 (sama seperti ApoImport::headingRow()).
        // ------------------------------------------------------
        $fullPath = Storage::path($tempPath);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
        $sheet = $spreadsheet->getSheetByName('Transaction Report')
            ?? $spreadsheet->getSheet(0);

        $rows = $sheet->toArray(null, true, true, false);

        // Baris 1-4 = judul/heading (heading row ke-4), data mulai baris ke-5
        $dataRows = array_slice($rows, 4);

        $bySerial = [];

        foreach ($dataRows as $row) {

            $caseId = trim((string) ($row[1] ?? ''));
            $serialNo = trim((string) ($row[6] ?? ''));

            if ($caseId === '' || $serialNo === '') {
                continue;
            }

            $bySerial[$serialNo][$caseId] = [
                'case_id'       => $caseId,
                'company_name'  => trim((string) ($row[3] ?? '')),
                'product_tower' => trim((string) ($row[7] ?? '')),
                'product_name'  => trim((string) ($row[9] ?? '')),
                'case_status'   => trim((string) ($row[11] ?? '')),
                'repair_action' => trim((string) ($row[13] ?? '')),
            ];
        }

        // Cuma Serial No dengan lebih dari 1 Case ID BERBEDA yang dianggap RR
        $rrGroups = [];

        foreach ($bySerial as $serialNo => $cases) {

            if (count($cases) > 1) {
                $rrGroups[$serialNo] = array_values($cases);
            }
        }

        // [DIUBAH] Sebelumnya return view('rr', ...) (pindah halaman). Sekarang
        // JSON, karena RR ditampilkan sebagai Langkah 2 di dalam popup yang
        // sama (tanpa refresh, seperti Google Form) — lihat import.blade.php.
        // Dikirim sebagai list (bukan array ber-key Serial No) supaya urutan
        // grup dan Case ID di dalamnya (Case ID terakhir = yang dapat dropdown
        // Keterangan) tidak berubah di sisi JavaScript.
        $groups = [];

        foreach ($rrGroups as $serialNo => $cases) {
            $groups[] = [
                'serial_no' => (string) $serialNo,
                'cases'     => $cases,
            ];
        }

        return response()->json([
            'kode_upload'  => $kodeUpload,
            'jenis_upload' => $request->jenis_upload,
            'rr_groups'    => $groups,
        ]);
    }

    // =========================================================
    // [DITAMBAHKAN] 🔥 RR (RE REPAIR) - CONFIRM / COMMIT
    // -----------------------------------------------------------------
    // Dipanggil dari tombol Export / Simpan / Simpan & Export di
    // halaman RR. Di sinilah data BENAR-BENAR masuk ke tabel apo,
    // pakai file yang sudah distash oleh rrPreview().
    // =========================================================
    public function rrConfirm(Request $request)
    {
        $request->validate([
            'kode_upload' => 'required',
            'action' => 'required|in:export,simpan,simpan_export',
        ]);

        $kodeUpload = $request->kode_upload;

        $stash = session("rr_upload.{$kodeUpload}");

        if (!$stash) {
            return redirect('/upload')->with(
                'error',
                'Sesi upload RR sudah kedaluwarsa, silakan upload ulang.'
            );
        }

        $fullPath = Storage::path($stash['temp_path']);

        // Bungkus path yang sudah distash jadi UploadedFile lagi, supaya
        // bisa dipakai persis seperti request()->file('file') biasa.
        $file = new \Illuminate\Http\UploadedFile(
            $fullPath,
            $stash['original_name'],
            null,
            null,
            true // test mode, supaya gak divalidasi ulang sebagai upload asli
        );

        if (in_array($request->action, ['simpan', 'simpan_export'])) {

            $this->commitApoUpload(
                $stash['jenis_upload'],
                $stash['kode_upload'],
                $file,
                $stash['apo_case_overrides'] ?? [],
                $stash['apo_keterangan_overrides'] ?? []
            );
        }

        // Bersihkan file & session sementara
        Storage::delete($stash['temp_path']);
        session()->forget("rr_upload.{$kodeUpload}");

        // TODO: kalau action = export / simpan_export, generate & kirim
        // file Excel hasil review di sini (mis. Excel::download(new
        // PoLaborExport(...), ...)). Belum diimplementasi — perlu
        // dikonfirmasi dulu format export yang diinginkan untuk RR.

        return redirect('/upload')->with(
            'success',
            'Data berhasil diupload!'
        );
    }
}
