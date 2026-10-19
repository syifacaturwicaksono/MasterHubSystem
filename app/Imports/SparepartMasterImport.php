<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/*
|--------------------------------------------------------------------------
| SparepartMasterImport
|--------------------------------------------------------------------------
| File Excel yang diupload biasanya berisi beberapa sheet (contoh:
| Sheet1 = data mentah transaksi, Sheet2 = pivot ringkasan, Sheet3 = daftar
| Part Number/Deskripsi/Kategori). Kita HANYA proses sheet bernama
| "Sheet3" — sheet lain diabaikan begitu saja (tidak bikin error).
|
| Kalau suatu saat nama sheet-nya beda-beda (tidak selalu "Sheet3"),
| kasih tahu Claude supaya logikanya disesuaikan (misal: deteksi sheet
| dari nama kolom header-nya, bukan dari nama sheet).
|--------------------------------------------------------------------------
*/

class SparepartMasterImport implements WithMultipleSheets
{
    public SparepartMasterSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new SparepartMasterSheetImport();
    }

    public function sheets(): array
    {
        return [
            'Sheet3' => $this->sheetImport,
        ];
    }
}
