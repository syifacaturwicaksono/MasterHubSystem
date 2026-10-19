<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MultiSheetImport;
use App\Models\UploadBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\Import\HandlesRrHistory;
use App\Http\Controllers\Concerns\Import\HandlesImportDataPages;
use App\Http\Controllers\Concerns\Import\HandlesBulkPage;
use App\Http\Controllers\Concerns\Import\HandlesPoLaborRevision;



class ImportController extends Controller
{
    use HandlesRrHistory,
        HandlesImportDataPages,
        HandlesBulkPage,
        HandlesPoLaborRevision;

    // =========================================================
    // 🔥 HALAMAN UPLOAD
    // =========================================================
    public function uploadPage()
    {
        $history = UploadBatch::latest()->paginate(10);

        // [DITAMBAHKAN] Daftar kategori labor, buat isi dropdown popup
        // klasifikasi manual part yang belum dikenal (khusus PO Labor).
        $laborCategories = DB::table('labor_categories')->orderBy('name')->get();

        return view('import', compact('history', 'laborCategories'));
    }

    // =========================================================
    // 🔥 IMPORT EXCEL
    // =========================================================
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'jenis_upload' => 'required',
            'kode_upload' => 'required'
        ]);

        // [DITAMBAHKAN] Pilihan part per Case ID (khusus PO Labor) yang sudah
        // dikonfirmasi/diganti user di popup review sebelum upload — kalau ada,
        // dipakai APERSIS itu, bukan hitung ulang otomatis di server.
        $apoCaseOverrides = [];

        if ($request->filled('apo_case_overrides')) {
            $decoded = json_decode($request->apo_case_overrides, true);
            $apoCaseOverrides = is_array($decoded) ? $decoded : [];
        }

        // [DITAMBAHKAN] Keterangan manual per Case ID dari popup review
        // (khusus case tanpa part / kategori manual). Kalau kosong,
        // ApoImport fallback ke Repair Action seperti biasa.
        $apoKeteranganOverrides = [];

        if ($request->filled('apo_keterangan_overrides')) {
            $decoded = json_decode($request->apo_keterangan_overrides, true);
            $apoKeteranganOverrides = is_array($decoded) ? $decoded : [];
        }

        $this->commitApoUpload(
            $request->jenis_upload,
            $request->kode_upload,
            $request->file('file'),
            $apoCaseOverrides,
            $apoKeteranganOverrides
        );

        return redirect('/upload')
            ->with('success', 'Data berhasil diupload!');
    }

    // =========================================================
    // [DITAMBAHKAN] 🔥 COMMIT KE DATABASE
    // -----------------------------------------------------------------
    // Dipisah dari import() supaya bisa dipakai ulang oleh rrConfirm()
    // (halaman RR), yang commit-nya ditunda sampai user klik
    // Simpan/Simpan & Export di halaman RR, bukan langsung dari popup.
    // Isinya PERSIS logic yang sebelumnya ada di dalam import().
    // =========================================================
    private function commitApoUpload(
        string $jenisUpload,
        string $kodeUpload,
        $file,
        array $apoCaseOverrides = [],
        array $apoKeteranganOverrides = []
    ) {
        if ($jenisUpload == 'W001-WIP') {
            DB::table('wip_datas')->truncate();
        }

        if ($jenisUpload == 'W003-TAT5D (Pending case 5 days)') {
            DB::table('pending')->truncate();
        }

        if ($jenisUpload == 'W002-TAT14D (Pending case 14 days)') {
            DB::table('pending_14d')->truncate();
        }

        if ($jenisUpload == 'W004-KCI') {
            DB::table('kci')->truncate();
        }

        if ($jenisUpload == 'W005-FR (Finish Repair)') {
            DB::table('finish_repair')->truncate();
        }
        if ($jenisUpload == 'M001-MPR') {
            DB::table('mpr_datas')->truncate();
        }
        if ($jenisUpload == 'M002-Reminder Invoice') {
            DB::table('reminder')->truncate();
        }
        if ($jenisUpload == 'M003-Pembayaran') {
            DB::table('pembayaran')->truncate();
        }

        // [DITAMBAHKAN] KLA
        if ($jenisUpload == 'D001-KLA') {
            DB::table('kla')->truncate();
        }

        // [DITAMBAHKAN] PO Labor / APO1
        if ($jenisUpload == 'M004-PO Labor') {
            DB::table('apo')->truncate();
        }

        // 🔥 SIMPAN HISTORY / BATCH
        UploadBatch::create([
            'jenis_upload' => $jenisUpload,
            'kode_upload' => $kodeUpload,
            'file_name' => $file->getClientOriginalName()
        ]);

        // [DITAMBAHKAN] RR (Re Repair): case dengan Serial No sama tapi
        // BUKAN kemunculan pertama di file ini -> Labor harus null.
        // Cuma relevan untuk PO Labor, scan langsung dari file yang
        // sama (sheet "Transaction Report").
        $rrRepeatCaseIds = [];

        if ($jenisUpload == 'M004-PO Labor') {
            $rrRepeatCaseIds = $this->scanRrRepeatCaseIds($file);
        }

        // 🔥 IMPORT SHEET 1
        //
        // [DITAMBAHKAN] $apoKeteranganOverrides & $rrRepeatCaseIds
        // diteruskan sebagai argumen ke-4 & ke-5, dan MultiSheetImport
        // sudah meneruskannya ke `new ApoImport($kodeUpload,
        // $keteranganOverrides, $rrRepeatCaseIds)` — keterangan manual
        // & aturan "RR gak dapat Labor" sekarang benar-benar tersimpan.
        Excel::import(
            new MultiSheetImport(
                $jenisUpload,
                $kodeUpload,
                $apoCaseOverrides,
                $apoKeteranganOverrides, // [DITAMBAHKAN]
                $rrRepeatCaseIds // [DITAMBAHKAN]
            ),
            $file
        );

        // [DITAMBAHKAN] Simpan semua case file ini ke apo_history (untuk cek RR
        // bulan-bulan berikutnya). Gagal simpan riwayat tidak membatalkan upload.
        if ($jenisUpload == 'M004-PO Labor') {
            try {
                $this->saveRrHistory(
                    $this->readTransactionRows($file->getRealPath()),
                    $kodeUpload
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Simpan apo_history gagal', ['error' => $e->getMessage()]);
            }
        }
    }

    // =========================================================
    // [DITAMBAHKAN] 🔥 RR LINTAS BULAN (JENDELA 30 HARI)
    // -----------------------------------------------------------------
    // Tabel apo di-truncate setiap upload PO Labor baru, jadi case bulan
    // lalu hilang. Tabel apo_history menyimpan semua case dari setiap
    // upload (dan dari "Upload Riwayat") KHUSUS untuk cek RR:
    //   RR kalau Serial No sama, Case ID beda, dan
    //   Received Date (case baru) - Closed Date (case lama) = 0..30 hari (RR_WINDOW_DAYS).
    // apo_history tidak dipakai BC Email.
    // =========================================================
    // Jendela RR: hanya case lama yang Closed <= 30 hari sebelum Received case baru.
    // Riwayat di apo_history boleh menyimpan 3 bulan atau lebih; yang menentukan
    // RR cuma angka ini. Ubah di sini kalau aturannya berubah.
    private const RR_WINDOW_DAYS = 30;

    private function parseExcelDate($value): ?\Carbon\Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return \Carbon\Carbon::instance($value)->startOfDay();
            }

            if (is_numeric($value)) {
                $n = (float) $value;

                // nomor seri tanggal Excel
                if ($n > 20000 && $n < 80000) {
                    return \Carbon\Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($n)
                    )->startOfDay();
                }

                return null;
            }

            $str = trim((string) $value);

            if ($str === '') {
                return null;
            }

            // dd-mm-yyyy atau dd/mm/yyyy (format Indonesia)
            if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/', $str, $m)) {
                return \Carbon\Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
            }

            // nama bulan Indonesia -> Inggris (mis. "25 Mei 2026 11:10")
            $str = str_ireplace(
                ['Mei', 'Agu', 'Agt', 'Okt', 'Des'],
                ['May', 'Aug', 'Aug', 'Oct', 'Dec'],
                $str
            );

            return \Carbon\Carbon::parse($str)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    // Baca sheet "Transaction Report". Kolom dicari lewat NAMA HEADER (bukan posisi
    // tetap), karena beberapa file punya kolom tambahan (mis. "Kode" di depan
    // "Case ID") sehingga posisinya bergeser. Kalau header tidak ketemu, dipakai
    // posisi standar: B=Case ID, D=Company, E=Received, G=Serial, X=Closed.
    private function readTransactionRows(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);

        $sheet = $spreadsheet->getSheetByName('Transaction Report')
            ?? $spreadsheet->getSheet(0);

        $text = $sheet->toArray(null, true, true, false);
        $raw  = $sheet->toArray(null, true, false, false);

        $norm = fn ($v) => preg_replace('/[^a-z0-9]/', '', strtolower((string) $v));

        // cari baris header (yang berisi "Case ID" dan "Serial no.") di 10 baris pertama
        $headerIdx = null;
        $map = [];

        foreach (array_slice($text, 0, 10, true) as $i => $row) {
            $cols = array_map($norm, $row);

            if (in_array('caseid', $cols, true) && in_array('serialno', $cols, true)) {
                $headerIdx = $i;

                foreach ($cols as $c => $name) {
                    if ($name !== '' && !isset($map[$name])) {
                        $map[$name] = $c;
                    }
                }
                break;
            }
        }

        if ($headerIdx === null) {
            // posisi standar (file tanpa kolom "Kode")
            $headerIdx = 3;
            $map = [
                'caseid' => 1, 'companyname' => 3, 'receiveddate' => 4,
                'warrantystatus' => 5, 'serialno' => 6, 'producttower' => 7,
                'productname' => 9, 'casestatus' => 11, 'repairaction' => 13,
                'closeddate' => 23,
            ];
        }

        $col = fn (string $name) => $map[$name] ?? null;

        $out = [];

        foreach (array_slice($text, $headerIdx + 1, null, true) as $i => $row) {

            $get = function (string $name) use ($row, $col) {
                $c = $col($name);
                return $c === null ? '' : trim((string) ($row[$c] ?? ''));
            };

            $getRaw = function (string $name) use ($raw, $i, $col) {
                $c = $col($name);
                return $c === null ? null : ($raw[$i][$c] ?? null);
            };

            $caseId = $get('caseid');
            $serial = $get('serialno');

            if ($caseId === '' || $serial === '' || isset($out[$caseId])) {
                continue;
            }

            $out[$caseId] = [
                'case_id'         => $caseId,
                'serial_no'       => $serial,
                'company_name'    => $get('companyname'),
                'received_date'   => $this->parseExcelDate($getRaw('receiveddate')),
                'closed_date'     => $this->parseExcelDate($getRaw('closeddate')),
                'product_tower'   => $get('producttower'),
                'product_name'    => $get('productname'),
                'case_status'     => $get('casestatus'),
                'warranty_status' => $get('warrantystatus'),
                'repair_action'   => $get('repairaction'),
            ];
        }

        return array_values($out);
    }
}