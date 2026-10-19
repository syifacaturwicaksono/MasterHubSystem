<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| PartCategoryResolver
|--------------------------------------------------------------------------
| ★★★ DIUBAH: sekarang TIDAK PERNAH auto-insert/auto-tebak diam-diam.
|
| Alur barunya:
| 1. resolve() cuma BACA part_master. Kalau part_no belum ada di situ,
|    hasilnya "belum dikenal" (known=false) — TIDAK ada apapun yang
|    ke-insert ke database.
| 2. Sebelum data upload diproses ke database, halaman Upload Sheet akan
|    cek semua part_no dulu (lewat endpoint checkParts). Part yang belum
|    dikenal ditampilkan di POPUP, user pilih kategorinya manual (dropdown
|    sudah di-prefill dengan tebakan lewat guessCategory(), tinggal
|    dikonfirmasi/diganti), baru user klik Simpan.
| 3. Baru setelah itu saveClassification() dipanggil, part_no itu resmi
|    masuk part_master dengan is_confirmed=true.
| 4. Part_no yang SAMA di upload berikutnya otomatis kepakai lagi tanpa
|    perlu diklasifikasi ulang (karena sudah ada di part_master).
|--------------------------------------------------------------------------
*/

class PartCategoryResolver
{
    /**
     * Urutan penting: dicek dari atas ke bawah, yang cocok duluan menang.
     * Kata kunci dicocokkan case-insensitive terhadap nama/deskripsi part.
     * Ini CUMA dipakai untuk SARAN tebakan di dropdown popup — bukan buat
     * auto-commit ke database.
     */
    private const KEYWORD_MAP = [
        'Mainboard'                     => ['MAINBOARD', ' MB ', '-MB', 'SPS-MB', 'MOTHERBOARD'],
        'LCD Monitor'                   => ['LCD MONITOR', 'MONITOR'],
        'LCD Panel'                     => ['LCD PANEL', 'LCD', 'DISPLAY PANEL'],
        'PSU'                           => ['PSU', 'POWER SUPPLY'],
        'Processor'                     => ['PROCESSOR', 'CPU INT', ' CPU '],
        'HDD'                           => ['HDD', 'SSD', 'HARD DISK', 'HARD DRIVE'],
        'Thin Client'                   => ['THIN CLIENT'],
        'Adaptor'                       => ['ADAPTOR', 'ADAPTER', 'CHARGER'],
        'Keyboard'                      => ['KEYBOARD', ' KB ', 'W/KB'],
        'Rebuild, Re install, Recovery' => ['REBUILD', 'REINSTALL', 'RE INSTALL', 'RECOVERY'],
        'Replace Unit'                  => ['REPLACE UNIT', 'REPLACEMENT UNIT'],
        'Upgrade Memory'                => ['MEMORY', 'RAM'],
    ];

    /**
     * [DIUBAH] Cuma BACA part_master, tidak pernah insert/tebak diam-diam.
     *
     * @return array{known: bool, category_id: ?int, category_name: ?string, price: ?float, is_confirmed: bool}
     */
    public static function resolve(
    string $partNo,
    ?string $partName,
    string $productTower,
    ?string $productName = null
): array {

    $partNo = trim($partNo);

    $partName = trim((string) $partName);

    $productName = strtoupper(
        trim((string) $productName)
    );

    /*
    |--------------------------------------------------------------------------
    | RULE KHUSUS PRINTER
    |--------------------------------------------------------------------------
    |
    | Kalau Product Name mengandung PRINTER,
    | langsung Simple Repair.
    |
    | Tidak peduli part number / part name.
    |--------------------------------------------------------------------------
    */

    if ($productName !== '' && str_contains($productName, 'PRINTER')) {

        $category = DB::table('labor_categories')
            ->where('name', 'Simple Repair')
            ->first();

        if ($category) {

            $priceColumn =
                strtoupper(trim($productTower)) === 'IPG'
                    ? 'ipg_price'
                    : 'psg_price';

            $price = $category->{$priceColumn} ?? null;

            return [
                'known'         => true,
                'category_id'   => $category->id,
                'category_name' => $category->name,
                'price'         => $price !== null
                    ? (float) $price
                    : null,
                'is_confirmed'  => true,
                'auto_rule'     => 'PRINTER',
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RULE NORMAL
    |--------------------------------------------------------------------------
    | Cocokkan PART NO + PART NAME
    |--------------------------------------------------------------------------
    */

    $existing = DB::table('part_master')
        ->where('part_no', $partNo)
        ->whereRaw(
            'UPPER(TRIM(part_name)) = ?',
            [strtoupper($partName)]
        )
        ->first();

    if (!$existing || !$existing->category_id) {

        // [DITAMBAHKAN] Belum ketemu (atau belum lengkap) di part_master -
        // sebelum divonis "belum dikenal", cek dulu ke sparepart_master
        // (master part yang diupload lewat menu Sparepart Master). Kalau
        // part_number-nya sudah ada di situ dengan kategori yang cocok ke
        // salah satu labor_categories, part ini dianggap SUDAH dikenal &
        // otomatis confirmed - user gak perlu klasifikasi ulang manual di
        // popup PO Labor untuk part yang memang sudah ada di Sparepart
        // Master.
        $fromSparepartMaster = self::resolveFromSparepartMaster(
            $partNo,
            $productTower
        );

        if ($fromSparepartMaster) {
            return $fromSparepartMaster;
        }

        return [
            'known'         => false,
            'category_id'   => null,
            'category_name' => null,
            'price'         => null,
            'is_confirmed'  => false,
            'auto_rule'     => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY
    |--------------------------------------------------------------------------
    */

    $category = DB::table('labor_categories')
        ->find($existing->category_id);

    if (!$category) {

        return [
            'known'         => false,
            'category_id'   => null,
            'category_name' => null,
            'price'         => null,
            'is_confirmed'  => false,
            'auto_rule'     => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PRICE
    |--------------------------------------------------------------------------
    */

    $priceColumn =
        strtoupper(trim($productTower)) === 'IPG'
            ? 'ipg_price'
            : 'psg_price';

    $price = $category->{$priceColumn} ?? null;

    return [
        'known'         => true,
        'category_id'   => $existing->category_id,
        'category_name' => $category->name,
        'price'         => $price !== null
            ? (float) $price
            : null,
        'is_confirmed'  => (bool) $existing->is_confirmed,
        'auto_rule'     => null,
    ];
}
    /**
     * [DITAMBAHKAN] Cek part_number ke tabel sparepart_master (master part
     * yang diupload lewat menu Sparepart Master, terpisah dari part_master
     * yang khusus dipakai alur PO Labor). Dipanggil dari resolve() sebagai
     * fallback kalau part_no belum/tidak ketemu di part_master.
     *
     * Harga TETAP dihitung ulang dari labor_categories + $productTower
     * (product tower dari baris transaksi PO Labor yang sedang diproses),
     * BUKAN dari kolom harga di sparepart_master - supaya konsisten dengan
     * cara resolve() menghitung harga di jalur part_master, dan tidak
     * kebawa harga lama kalau labor_categories berubah setelah part itu
     * diupload ke Sparepart Master.
     *
     * @return array{known: bool, category_id: int, category_name: string, price: ?float, is_confirmed: bool, auto_rule: string}|null
     *         null kalau part_number tidak ketemu di sparepart_master, atau
     *         kategorinya di sana tidak cocok dengan nama manapun di
     *         labor_categories.
     */
    private static function resolveFromSparepartMaster(
        string $partNo,
        string $productTower
    ): ?array {

        $sparepart = DB::table('sparepart_master')
            ->where('part_number', $partNo)
            ->first();

        if (!$sparepart || empty($sparepart->kategori)) {
            return null;
        }

        $category = DB::table('labor_categories')
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [strtolower(trim($sparepart->kategori))]
            )
            ->first();

        if (!$category) {
            return null;
        }

        $priceColumn =
            strtoupper(trim($productTower)) === 'IPG'
                ? 'ipg_price'
                : 'psg_price';

        $price = $category->{$priceColumn} ?? null;

        return [
            'known'         => true,
            'category_id'   => $category->id,
            'category_name' => $category->name,
            'price'         => $price !== null
                ? (float) $price
                : null,
            'is_confirmed'  => true,
            'auto_rule'     => 'SPAREPART_MASTER',
        ];
    }

    /**
     * Tebak nama kategori dari teks deskripsi part, berdasarkan kata kunci.
     * Kalau gak ada yang cocok, fallback ke "Other Part". Dipakai CUMA untuk
     * saran di dropdown popup klasifikasi manual — bukan auto-commit.
     */
    public static function guessCategory(string $partName): string
    {
        $upper = ' ' . strtoupper($partName) . ' ';

        foreach (self::KEYWORD_MAP as $categoryName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($upper, strtoupper($keyword))) {
                    return $categoryName;
                }
            }
        }

        return 'Other Part';
    }

    /**
     * [BARU] Simpan klasifikasi manual yang sudah dikonfirmasi user lewat
     * popup ke part_master (is_confirmed selalu true, karena manusia yang pilih).
     * Kalau part_no sudah ada sebelumnya, kategori & konfirmasinya di-update
     * (bukan bikin baris dobel).
     */
    public static function saveClassification(string $partNo, ?string $partName, int $categoryId): void
    {
        $partNo = trim($partNo);

        $existing = DB::table('part_master')->where('part_no', $partNo)->first();

        if ($existing) {
            DB::table('part_master')->where('part_no', $partNo)->update([
                'part_name'    => $partName ?? $existing->part_name,
                'category_id'  => $categoryId,
                'is_confirmed' => true,
                'updated_at'   => now(),
            ]);
        } else {
            DB::table('part_master')->insert([
                'part_no'      => $partNo,
                'part_name'    => $partName,
                'category_id'  => $categoryId,
                'is_confirmed' => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}