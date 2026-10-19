<?php

namespace App\Imports;

use App\Models\Apo;
use App\Services\PartLaborMapping;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ApoImport implements ToModel, WithHeadingRow
{
    protected $kodeUpload;

    // [DITAMBAHKAN] case_id => teks keterangan manual dari popup review.
    // Kalau case_id tidak ada di sini, fallback ke repair_action seperti
    // sebelumnya. Controller yang membuat ApoImport perlu decode
    // request('apo_keterangan_overrides') (JSON: {case_id: "teks"}) dan
    // meneruskannya ke sini.
    protected $keteranganOverrides;

    // [DITAMBAHKAN] Set case_id (case_id => true) yang merupakan RR
    // (Serial No sama dengan case lain di file yang sama, TAPI BUKAN
    // kemunculan pertama). Case pertama/original tetap dapat Labor
    // seperti biasa; case ke-2/ke-3 dst di set ini SELALU Labor null,
    // walaupun kategorinya kena aturan lain (printer/whole unit/dst).
    // Controller yang membuat ApoImport perlu scan sheet "Transaction
    // Report" (Case ID index 1, Serial No index 6) untuk membangun set
    // ini sebelum instantiate class ini - lihat komentar contoh di
    // ImportController::commitApoUpload().
    protected $rrRepeatCaseIds;

    public function __construct(
        $kodeUpload,
        array $keteranganOverrides = [],
        array $rrRepeatCaseIds = []
    ) {
        $this->kodeUpload = $kodeUpload;
        $this->keteranganOverrides = $keteranganOverrides;
        $this->rrRepeatCaseIds = $rrRepeatCaseIds;
    }

    public function headingRow(): int
    {
        return 4;
    }

    public function model(array $row)
{
    // ============================================================
    // DATA UTAMA (case_id & case_status dipindah ke atas karena
    // dibutuhkan duluan untuk cek aturan CV. SINAR PUTRA PERSADA / 511)
    // ============================================================

    $caseId = trim(
        (string) ($row['case_id'] ?? '')
    );

    // [DITAMBAHKAN] dibutuhkan untuk aturan "Close Repair tanpa part" &
    // untuk cek Sales Delay di bawah
    $caseStatus = trim(
        (string) ($row['case_status'] ?? '')
    );

    // [DITAMBAHKAN] CV. SINAR PUTRA PERSADA - semua Case ID berawalan
    // "511" selalu perusahaan ini (sudah dicek: 216/216 case awalan 511
    // di data konsisten CV. Sinar Putra Persada).
    $isCase511 = str_starts_with($caseId, '511');

    // [DITAMBAHKAN] ⚠️ Sebelumnya (SALAH) dicek dari Case Status. Ternyata
    // dari data asli, "Sales Delay" itu bagian dari WARRANTY STATUS itu
    // sendiri: nilainya "Out Warranty-SD" (bukan cuma "Out Warranty" atau
    // "In Warranty"). Jadi variabel ini dipindah & dihitung ulang di
    // bawah, setelah $warrantyStatus diambil.

    // ============================================================
    // WARRANTY
    // ============================================================

    $warrantyStatusUpper = strtoupper(
        trim((string) ($row['warranty_status'] ?? ''))
    );

    // [DIUBAH] Sebelumnya exact match 'out warranty' (strtolower) - gak
    // ke-cover nilai "Out Warranty-SD" karena stringnya beda persis.
    // Sekarang dicek pakai starts_with, disamakan dengan
    // isOutWarrantyStatus() di import_blade.php.
    $isOutWarranty = str_starts_with(
        $warrantyStatusUpper,
        'OUT WARRANTY'
    );

    // [DIUBAH] Sales Delay = Warranty Status "Out Warranty-SD". Disamakan
    // dengan isSalesDelayWarranty() di import_blade.php.
    $isSalesDelay = $isOutWarranty
        && str_contains($warrantyStatusUpper, 'SD');

    // Skip jika Out Warranty ...
    // [DIUBAH] ... KECUALI case awalan 511 (CV. Sinar Putra Persada) yang
    // statusnya Sales Delay ("Out Warranty-SD") - case itu tetap muncul,
    // Labor flat 170.000 (lihat blok "CV. SINAR PUTRA PERSADA" di bawah).
    // Case 511 yang OW tapi BUKAN Sales Delay tetap disembunyikan seperti
    // biasa, begitu juga semua case selain 511 yang OW (apapun sub-status
    // OW-nya, termasuk "Out Warranty-SD" milik partner lain).
    if ($isOutWarranty) {
        if (!($isCase511 && $isSalesDelay)) {
            return null;
        }
    }


    // ============================================================
    // DATA LAINNYA
    // ============================================================

    $productType = trim(
        (string) ($row['product_type'] ?? '')
    );

    $productName = trim(
        (string) ($row['product_name'] ?? '')
    );

    $repairAction = trim(
        (string) ($row['repair_action'] ?? '')
    );

    $productTower = trim(
        (string) ($row['product_tower'] ?? '')
    );

    // ============================================================
    // AMBIL HASIL DARI PARTS
    // ============================================================

    $laborData = PartLaborMapping::$mapping[$caseId] ?? null;

    // [DITAMBAHKAN] Catat dulu apakah case ini benar-benar TANPA part di
    // mapping (sebelum $laborData ditimpa aturan lain di bawah). Dipakai
    // untuk label "Close Repair tanpa part" di kolom Keterangan.
    $noPartInMapping = ($laborData === null);

    /*
    |--------------------------------------------------------------------------
    | HITUNG ULANG HARGA BERDASARKAN PRODUCT TOWER
    |--------------------------------------------------------------------------
    | ★ FIX: blok ini sebelumnya dijalankan SEBELUM $laborData diisi dari
    | mapping di atas, jadi $laborData selalu null saat dicek dan harga
    | tidak pernah dihitung ulang -> Labor yang tersimpan cuma hasil mentah
    | dari ApoPartsImport, bukan hasil recalculate sesuai IPG/PSG dari sheet
    | Transaction Report. Sekarang dipindah ke SETELAH pengambilan mapping.
    */

    if (is_array($laborData) && !empty($laborData['category'])) {

        $categoryPrice = $this->getCategoryPrice(
            $laborData['category'],
            $productTower
        );

        if ($categoryPrice) {

            $laborData['labor'] = $categoryPrice['price'];
        }
    }


    // ============================================================
    // PRIORITAS KHUSUS
    //
    // 1. PRINTER                -> Simple Repair
    // 2. WHOLE UNIT             -> Replace Unit
    // 3. CLOSE REPAIR TANPA PART -> Rebuild, Re install, Recovery [DITAMBAHKAN]
    // 4. PART DEFECTIVE         -> hasil ApoPartsImport
    // ============================================================

    $override = null;


    // ------------------------------------------------------------
    // PRINTER
    // ------------------------------------------------------------
    //
    // Aturan:
    // Jika PRODUCT NAME mengandung "PRINTER"
    // maka selalu Simple Repair.
    //
    // Tidak peduli part yang diganti.
    // ------------------------------------------------------------

    // [DIUBAH] Printer & Whole Unit HANYA berlaku kalau case punya part di
    // mapping. Case TANPA part mengikuti aturan "Close Repair tanpa part"
    // (Rebuild, Re install, Recovery) supaya sama persis dengan yang
    // tampil di popup review (mis. IPG tanpa part = tanpa Labor, walau
    // Product Name-nya mengandung "Printer").
    if (!$noPartInMapping && $this->isPrinter($productName)) {

        $override = $this->getCategoryPrice(
            'Simple Repair',
            $productTower
        );

    }

    // ------------------------------------------------------------
    // WHOLE UNIT
    // ------------------------------------------------------------

    elseif (!$noPartInMapping && $this->isWholeUnitReplacement($repairAction)) {

        $override = $this->getCategoryPrice(
            'Replace Unit',
            $productTower
        );
    }

    // ------------------------------------------------------------
    // [DITAMBAHKAN] CLOSE REPAIR TANPA PART
    // ------------------------------------------------------------
    //
    // Aturan:
    // Case yang SAMA SEKALI GAK ADA PART (tidak ketemu di ApoPartsImport,
    // $laborData masih null di titik ini) tapi status-nya mengandung
    // "Close Repair" -> otomatis "Rebuild, Re install, Recovery".
    // Kategori ini di tabel labor_categories: PSG=50rb, IPG=NULL, jadi
    // IPG otomatis gak dapat labor tanpa perlu aturan harga terpisah.
    // ------------------------------------------------------------

    elseif ($noPartInMapping && str_contains(strtolower($caseStatus), 'close repair')) {

        $override = $this->getCategoryPrice(
            'Rebuild, Re install, Recovery',
            $productTower
        );
    }


    // ============================================================
    // JIKA ADA OVERRIDE
    // ============================================================

    if ($override) {

        // ★ FIX: sebelumnya part_no & part_name ikut dikosongkan di sini
        // padahal itu cuma informasi part yang diganti (bukan penentu
        // kategori/harga saat override kena). Sekarang dipertahankan dari
        // $laborData yang sudah didapat dari mapping ApoPartsImport
        // sebelumnya (kalau ada), cuma labor & kategori yang diganti.
        $laborData = [
            'labor'     => $override['price'],
            'part_no'   => $laborData['part_no'] ?? null,
            'part_name' => $laborData['part_name'] ?? null,
            'category'  => $override['category'],
        ];
    }


    // ============================================================
    // [DITAMBAHKAN] CV. SINAR PUTRA PERSADA (case awalan "511")
    // ------------------------------------------------------------
    // Labor selalu flat Rp170.000 untuk semua case awalan 511, apapun
    // kondisinya - IPG atau PSG, ada part atau tidak, kena aturan
    // Printer/Whole Unit/Close Repair tanpa part atau tidak. Ini
    // MENIMPA nilai Labor yang sudah dihitung di atas (dari mapping
    // part, category price, atau override Printer/Whole Unit/dst).
    // Part No/Part Name/Kategori TETAP disimpan apa adanya, cuma Labor
    // yang diganti flat. RR (blok di bawah ini) tetap menang - kalau
    // case 511 ini ternyata repeat (Serial No sama), Labor tetap
    // dikosongkan seperti case lain.
    // ------------------------------------------------------------

    if ($isCase511) {

        $laborData = array_merge(
            $laborData ?? [],
            ['labor' => 170000]
        );
    }


    // ============================================================
    // [DITAMBAHKAN] RR (RE REPAIR) - KOSONGKAN LABOR
    // ------------------------------------------------------------
    // Case yang merupakan repeat (Serial No sama, BUKAN kemunculan
    // pertama di file ini) selalu Labor null, apapun kategorinya -
    // termasuk override Printer/Whole Unit/Close Repair di atas.
    // Part No/Part Name/Kategori tetap disimpan apa adanya, cuma
    // Labor yang dikosongkan, supaya case ini tetap kelihatan di
    // tabel/BC tapi gak dihitung dobel di PO Labor.
    // ------------------------------------------------------------

    // [DITAMBAHKAN] Case berstatus Cancel (termasuk awalan 511 / SPP yang
    // tadinya kena flat 170.000) juga TIDAK dapat Labor, sama seperti
    // case Cancel lainnya.
    $isCancel = str_contains(strtolower($caseStatus), 'cancel');

    $manualKeterangan = trim(
        (string) ($this->keteranganOverrides[$caseId] ?? '')
    );

    // [DITAMBAHKAN] RR yang dipilih "Dapat Labor" di Langkah 1 popup review
    // tetap ditandai RR, tapi Labor-nya TIDAK dikosongkan (mis. case
    // pertama Cancel, case berikutnya order part). Cancel tetap tanpa Labor.
    $rrGetsLabor = isset($this->rrRepeatCaseIds[$caseId])
        && preg_match('/dapat labor/i', $manualKeterangan);

    if (
        (isset($this->rrRepeatCaseIds[$caseId]) && !$rrGetsLabor)
        || $isCancel
    ) {

        $laborData = array_merge(
            $laborData ?? [],
            ['labor' => null]
        );
    }


    // ============================================================
    // [DITAMBAHKAN] KETERANGAN untuk BC / Bulk Email
    // ------------------------------------------------------------
    // - RR (case ke-2, ke-3, dst dalam 1 Serial No) -> muncul "RR".
    //   Kalau user pilih/isi Keterangan di popup review, itu yang
    //   dipakai (dipastikan mengandung kata "RR").
    // - Close Repair TANPA part -> muncul "Close Repair tanpa part"
    //   (ditambah teks manual dari popup kalau ada).
    // - Selain itu: teks manual dari popup, kalau kosong fallback ke
    //   repair_action seperti sebelumnya.
    // ============================================================

    $isRrRepeat = isset($this->rrRepeatCaseIds[$caseId]);

    $isCloseRepairNoPart = $noPartInMapping
        && str_contains(strtolower($caseStatus), 'close repair');


    // [DITAMBAHKAN] Kategori "Void Case" dari popup review dikirim lewat
    // Keterangan (berisi kata "Void Case"). Case void = TANPA Labor, apapun
    // aturan lain (termasuk awalan 511), dan kategorinya disimpan "Void Case".
    if (preg_match('/\bvoid case\b/i', $manualKeterangan)) {

        $laborData = array_merge(
            $laborData ?? [],
            ['labor' => null, 'category' => 'Void Case']
        );
    }

    if ($isRrRepeat) {

        if ($manualKeterangan === '') {
            $keteranganFinal = 'RR';
        } elseif (preg_match('/\bRR\b/i', $manualKeterangan)) {
            $keteranganFinal = $manualKeterangan;
        } else {
            $keteranganFinal = 'RR - ' . $manualKeterangan;
        }

    } elseif ($isCloseRepairNoPart) {

        $keteranganFinal = $manualKeterangan === ''
            ? 'Close Repair tanpa part'
            : 'Close Repair tanpa part - ' . $manualKeterangan;

    } elseif ($manualKeterangan !== '') {

        $keteranganFinal = $manualKeterangan;

    } else {

        $keteranganFinal = $row['repair_action'] ?? null;
    }


    // ============================================================
    // SIMPAN
    // ============================================================

    return new Apo([

        'jenis_monitoring' => 'M004-PO Labor',
        'kode_upload'      => $this->kodeUpload,

        'case_id'           => $row['case_id'] ?? null,
        'case_id_manual'    => $row['case_id_manual'] ?? null,
        'company_name'      => $row['company_name'] ?? null,

        'received_date' => $this->formatDate(
            $row['received_date'] ?? null
        ),

        'warranty_status' => $row['warranty_status'] ?? null,
        'serial_no'      => $row['serial_no'] ?? null,

        'product_tower' => $row['product_tower'] ?? null,
        'product_no'    => $row['product_no'] ?? null,
        'product_name'  => $row['product_name'] ?? null,

        'case_type'   => $row['case_type'] ?? null,
        'case_status' => $row['case_status'] ?? null,

        'problem_desc' => $row['problem_desc'] ?? null,
        'repair_action' => $row['repair_action'] ?? null,

        'customer_company' => $row['customer_company'] ?? null,
        'customer_name'    => $row['customer_name'] ?? null,
        'pic_name'        => $row['pic_name'] ?? null,

        'city'      => $row['city'] ?? null,
        'phone_no' => $row['phone_no'] ?? null,
        'mobile_no' => $row['mobile_no'] ?? null,

        'start_repair_date' => $this->formatDate(
            $row['start_repair_date'] ?? null
        ),

        'tat_case' => $row['tat_case_received_finish'] ?? null,

        'finish_date' => $this->formatDate(
            $row['finish_date'] ?? null
        ),

        'closed_date' => $this->formatDate(
            $row['closed_date'] ?? null
        ),

        // ========================================================
        // LABOR
        // ========================================================

        'labor' => $laborData['labor'] ?? null,

        'part_no' => $laborData['part_no'] ?? null,

        'part_name' => $laborData['part_name'] ?? null,

        'part_category' => $laborData['category'] ?? null,

        // ★ FIX: sebelumnya ambil dari kolom Excel "Remarks", sekarang
        // diganti ambil dari "Repair Action" sesuai permintaan.
        // [DITAMBAHKAN] Kalau user isi Keterangan manual di popup review
        // untuk case_id ini, pakai itu; kalau tidak, fallback ke
        // repair_action seperti sebelumnya.
        'keterangan' => $keteranganFinal,
    ]);
}
    // [DITAMBAHKAN] Product type printer HP (bukan PC/Notebook)
   private function isPrinter(string $productName): bool
{
    $text = strtoupper(trim($productName));

    return str_contains($text, 'PRINTER');
}

private function formatDate($value)
{
    if (empty($value)) {
        return null;
    }

    // Jika sudah berupa DateTime
    if ($value instanceof \DateTimeInterface) {
        return $value->format('Y-m-d');
    }

    // Jika Excel mengirim angka serial date
    if (is_numeric($value)) {
        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                ->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    // Jika string tanggal
    try {
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    } catch (\Throwable $e) {
        return null;
    }
}

    // [DITAMBAHKAN] Deteksi dari teks bebas "Repair action" apakah ini
    // penggantian unit utuh (bukan cuma ganti 1 part). Kalau nanti ada
    // kalimat lain yang perlu ditangkap, tinggal tambah ke daftar kata kunci ini.


    private function isWholeUnitReplacement(string $repairAction): bool
    {
        $text = strtolower($repairAction);

        $keywords = [
            'whole unit',
            'unit exchange',
            'ganti unit',
            'penggantian unit',
            'tukar unit',
            'replace unit',
            'replacement unit',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    // [DITAMBAHKAN] Ambil harga dari tabel labor_categories untuk 1 kategori,
    // sesuai kolom PSG/IPG (dari Product tower).
    private function getCategoryPrice(string $categoryName, string $productTower): ?array
    {
        $category = DB::table('labor_categories')
            ->where('name', $categoryName)
            ->first();

        if (!$category) {
            return null;
        }

        // [DITAMBAHKAN] Disamakan PERSIS dengan computeLaborPrice() di
        // import_blade.php: kategori "Other Part" selalu Rp50.000, baik
        // PSG maupun IPG (tidak lihat kolom psg_price/ipg_price).
        if (strtolower(trim($categoryName)) === 'other part') {
            return [
                'category' => $categoryName,
                'price'    => 50000.0,
            ];
        }

        $priceColumn = strtoupper(trim($productTower)) === 'IPG' ? 'ipg_price' : 'psg_price';
        $price = $category->{$priceColumn};

        return [
            'category' => $categoryName,
            'price'    => $price !== null ? (float) $price : null,
        ];
    }
}