<?php

namespace App\Http\Controllers;

use App\Imports\SparepartMasterImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SparepartMasterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SparepartMasterController
    |--------------------------------------------------------------------------
    | Harga sparepart otomatis mengikuti:
    |
    | PSG + Kategori = labor_categories.psg_price
    | IPG + Kategori = labor_categories.ipg_price
    |
    | User tidak perlu memilih harga secara manual.
    |--------------------------------------------------------------------------
    */

    private const DIVISI_OPTIONS = ['IPG', 'PSG'];

    /**
     * Ambil seluruh kategori beserta harga IPG dan PSG.
     *
     * Dipakai oleh Blade/JavaScript agar ketika kategori atau divisi
     * berubah, harga dapat langsung dihitung otomatis.
     */
    private function getLaborCategories()
    {
        return DB::table('labor_categories')
            ->select(
                'name',
                'psg_price',
                'ipg_price'
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * Cari harga berdasarkan kategori + divisi.
     *
     * Return null jika kategori/divisi tidak ditemukan.
     */
    private function getHargaByKategoriDivisi($kategori, $divisi)
    {
        if (empty($kategori) || empty($divisi)) {
            return null;
        }

        $kategori = trim($kategori);
        $divisi = strtoupper(trim($divisi));

        $category = DB::table('labor_categories')
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [strtolower($kategori)]
            )
            ->first();

        if (!$category) {
            return null;
        }

        if ($divisi === 'PSG') {
            return $category->psg_price;
        }

        if ($divisi === 'IPG') {
            return $category->ipg_price;
        }

        return null;
    }

    /**
     * INDEX
     */

public function index()
{
    $spareparts = DB::table('sparepart_master')
        ->orderByDesc('created_at')
        ->paginate(25);

    /*
     * Ambil kategori + harga IPG/PSG
     */
    $laborCategories = $this->getLaborCategories();

    /*
     * Tetap kirim hargaOptions karena Blade lama
     * masih membutuhkan variabel ini.
     */
    $hargaOptions = $laborCategories
        ->pluck('psg_price')
        ->merge(
            $laborCategories->pluck('ipg_price')
        )
        ->filter(function ($price) {
            return !is_null($price);
        })
        ->unique()
        ->sort()
        ->values();

    return view('sparepart_master', [
        'spareparts'    => $spareparts,

        /*
         * categories sekarang object lengkap:
         * name, psg_price, ipg_price
         */
        'categories'    => $laborCategories,

        'divisiOptions' => self::DIVISI_OPTIONS,

        /*
         * Tetap tersedia agar Blade tidak error.
         */
        'hargaOptions'  => $hargaOptions,
    ]);
}



    /**
     * PREVIEW EXCEL
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new SparepartMasterImport();

        Excel::import(
            $import,
            $request->file('file')
        );

        $parsedRows = $import->sheetImport->rows;

        if (empty($parsedRows)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Tidak ada data terbaca dari sheet "Sheet3". '
                    . 'Pastikan file punya sheet bernama "Sheet3" dengan kolom '
                    . 'Part Number, Part Description, Product tower, dan Kategori, '
                    . 'data mulai baris ke-4.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 1. HILANGKAN DUPLIKAT PART NUMBER DARI EXCEL
        |--------------------------------------------------------------------------
        */

        $uniqueRows = [];

        foreach ($parsedRows as $row) {

            $partNumber = trim(
                $row['part_number'] ?? ''
            );

            if ($partNumber === '') {
                continue;
            }

            if (!isset($uniqueRows[$partNumber])) {
                $uniqueRows[$partNumber] = $row;
            }
        }

        if (empty($uniqueRows)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada Part Number yang valid dari file Excel.',
            ], 422);
        }

        $partNumbers = array_keys($uniqueRows);

        /*
        |--------------------------------------------------------------------------
        | 2. CEK PART NUMBER YANG SUDAH ADA
        |--------------------------------------------------------------------------
        */

        $existingPartNumbers = collect($partNumbers)
            ->chunk(1000)
            ->flatMap(function ($chunk) {

                return DB::table('sparepart_master')
                    ->whereIn(
                        'part_number',
                        $chunk->all()
                    )
                    ->pluck('part_number');

            })
            ->flip();

        /*
        |--------------------------------------------------------------------------
        | 3. AMBIL KATEGORI + HARGA
        |--------------------------------------------------------------------------
        */

        $laborCategories = $this->getLaborCategories();

        /*
         * Mapping kategori lowercase agar pencarian tidak sensitif
         * terhadap huruf besar/kecil.
         */
        $categoriesLower = $laborCategories
            ->mapWithKeys(function ($category) {

                return [
                    strtolower(
                        trim($category->name)
                    ) => $category
                ];

            });

        /*
        |--------------------------------------------------------------------------
        | 4. PROSES PREVIEW
        |--------------------------------------------------------------------------
        */

        $result = [];

        $newCount = 0;

        $no = 0;

        foreach ($uniqueRows as $row) {

            $no++;

            $partNumber = trim(
                $row['part_number'] ?? ''
            );

            $isNew = !isset(
                $existingPartNumbers[$partNumber]
            );

            if ($isNew) {
                $newCount++;
            }

            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            */

            $kategoriExcel = trim(
                $row['kategori'] ?? ''
            );

            $categoryData = $categoriesLower[
                strtolower($kategoriExcel)
            ] ?? null;

            $matchedKategori = $categoryData
                ? $categoryData->name
                : null;

            /*
            |--------------------------------------------------------------------------
            | DIVISI
            |--------------------------------------------------------------------------
            */

            $divisiExcel = strtoupper(
                trim($row['divisi'] ?? '')
            );

            $matchedDivisi = in_array(
                $divisiExcel,
                self::DIVISI_OPTIONS,
                true
            )
                ? $divisiExcel
                : null;

            /*
            |--------------------------------------------------------------------------
            | HARGA OTOMATIS
            |--------------------------------------------------------------------------
            */

            $hargaOtomatis = null;

            if ($categoryData && $matchedDivisi) {

                if ($matchedDivisi === 'PSG') {

                    $hargaOtomatis =
                        $categoryData->psg_price;

                } elseif ($matchedDivisi === 'IPG') {

                    $hargaOtomatis =
                        $categoryData->ipg_price;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | HASIL PREVIEW
            |--------------------------------------------------------------------------
            */

            $result[] = [

                'no' => $no,

                'part_number' =>
                    $partNumber,

                'deskripsi' =>
                    $row['deskripsi'] ?? '',

                'divisi_excel' =>
                    $row['divisi'] ?? '',

                'divisi_matched' =>
                    $matchedDivisi,

                'kategori_excel' =>
                    $row['kategori'] ?? '',

                'kategori_matched' =>
                    $matchedKategori,

                /*
                 * Harga otomatis dari kategori + divisi.
                 */
                'harga' =>
                    $hargaOtomatis,

                'is_new' =>
                    $isNew,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 5. RETURN JSON
        |--------------------------------------------------------------------------
        |
        | PENTING:
        | categories sekarang dikirim sebagai OBJECT lengkap,
        | bukan hanya pluck('name').
        |
        | Contoh:
        |
        | {
        |   name: "LCD",
        |   psg_price: 150000,
        |   ipg_price: 100000
        | }
        |
        */

        return response()->json([

            'success' =>
                true,

            'rows' =>
                $result,

            'categories' =>
                $laborCategories,

            'divisiOptions' =>
                self::DIVISI_OPTIONS,

            'summary' => [

                'total' =>
                    count($result),

                'new' =>
                    $newCount,

                'existing' =>
                    count($result) - $newCount,
            ],
        ]);
    }

    /**
     * STORE DATA BARU
     */
    public function store(Request $request)
    {
        $request->validate([

            'rows' =>
                'required|array|min:1',

            'rows.*.part_number' =>
                'required|string',

            'rows.*.deskripsi' =>
                'nullable|string',

            'rows.*.kategori' =>
                'nullable|string',

            'rows.*.divisi' =>
                'nullable|string',
        ]);

        $rows = $request->input('rows');

        /*
        |--------------------------------------------------------------------------
        | CEK PART NUMBER YANG SUDAH ADA
        |--------------------------------------------------------------------------
        */

        $partNumbers = collect($rows)
            ->pluck('part_number')
            ->map(function ($value) {
                return trim($value);
            })
            ->filter()
            ->unique()
            ->values();

        $alreadyExists = $partNumbers
            ->chunk(1000)
            ->flatMap(function ($chunk) {

                return DB::table('sparepart_master')
                    ->whereIn(
                        'part_number',
                        $chunk->all()
                    )
                    ->pluck('part_number');

            })
            ->flip();

        $now = now();

        $toInsert = [];

        foreach ($rows as $row) {

            $partNumber = trim(
                $row['part_number'] ?? ''
            );

            if ($partNumber === '') {
                continue;
            }

            /*
             * Kalau sudah ada, jangan diubah.
             */
            if (
                isset(
                    $alreadyExists[$partNumber]
                )
            ) {
                continue;
            }

            $kategori = trim(
                $row['kategori'] ?? ''
            );

            $divisi = strtoupper(
                trim($row['divisi'] ?? '')
            );

            /*
            |--------------------------------------------------------------------------
            | HARGA DIHITUNG ULANG DI SERVER
            |--------------------------------------------------------------------------
            |
            | Jangan menggunakan harga yang dikirim JavaScript.
            | Harga selalu mengikuti kategori + divisi.
            |
            */

            $harga = $this->getHargaByKategoriDivisi(
                $kategori,
                $divisi
            );

            $toInsert[] = [

                'part_number' =>
                    $partNumber,

                'deskripsi' =>
                    $row['deskripsi'] ?? null,

                'kategori' =>
                    $kategori !== ''
                        ? $kategori
                        : null,

                'divisi' =>
                    in_array(
                        $divisi,
                        self::DIVISI_OPTIONS,
                        true
                    )
                        ? $divisi
                        : null,

                'harga' =>
                    $harga,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | INSERT BATCH
        |--------------------------------------------------------------------------
        */

        if (!empty($toInsert)) {

            foreach (
                array_chunk(
                    $toInsert,
                    500
                ) as $chunk
            ) {

                DB::table(
                    'sparepart_master'
                )->insert($chunk);
            }
        }

        return response()->json([

            'success' =>
                true,

            'inserted' =>
                count($toInsert),

            'skipped' =>
                count($rows) -
                count($toInsert),
        ]);
    }

    /**
     * UPDATE DATA
     */
    public function update(
        Request $request,
        $id
    ) {
        $request->validate([

            'deskripsi' =>
                'nullable|string',

            'kategori' =>
                'nullable|string',

            'divisi' =>
                'nullable|string',
        ]);

        $kategori = trim(
            $request->kategori ?? ''
        );

        $divisi = strtoupper(
            trim($request->divisi ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | HARGA OTOMATIS
        |--------------------------------------------------------------------------
        |
        | Harga TIDAK diambil dari request.
        | Server menghitung ulang berdasarkan:
        |
        | kategori + divisi
        |
        */

        $harga = $this->getHargaByKategoriDivisi(
            $kategori,
            $divisi
        );

        DB::table('sparepart_master')
            ->where('id', $id)
            ->update([

                'deskripsi' =>
                    $request->deskripsi,

                'kategori' =>
                    $kategori !== ''
                        ? $kategori
                        : null,

                'divisi' =>
                    in_array(
                        $divisi,
                        self::DIVISI_OPTIONS,
                        true
                    )
                        ? $divisi
                        : null,

                'harga' =>
                    $harga,

                'updated_at' =>
                    now(),
            ]);

        return response()->json([

            'success' =>
                true,

            /*
             * Kirim harga hasil perhitungan
             * supaya frontend langsung menampilkan
             * harga yang benar.
             */
            'harga' =>
                $harga,
        ]);
    }
}
