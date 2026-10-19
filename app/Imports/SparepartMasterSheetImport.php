<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/*
|--------------------------------------------------------------------------
| SparepartMasterSheetImport
|--------------------------------------------------------------------------
| Baca isi sheet "Sheet3" dari file export Part_HP.xlsx (atau file sejenis
| dengan struktur sama): kolom No, Part Number, Part Description,
| Product tower (divisi IPG/PSG), Kategori — data mulai baris ke-4 Excel
| (2 baris kosong + 1 baris header dilewati).
|
| Hasil parsing ditampung di properti $rows (bukan langsung insert ke DB),
| karena alurnya: parse -> tampilkan popup preview -> baru user konfirmasi
| -> baru benar-benar disimpan (lihat SparepartMasterController).
|--------------------------------------------------------------------------
*/

class SparepartMasterSheetImport implements ToCollection
{
    public array $rows = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // Lewati 2 baris kosong + 1 baris header (index 0, 1, 2)
            if ($index < 3) {
                continue;
            }

            $partNumber = trim((string) ($row[1] ?? ''));

            // Lewati baris kosong (misal baris terakhir file)
            if ($partNumber === '') {
                continue;
            }

            $this->rows[] = [
                'part_number' => $partNumber,
                'deskripsi'   => trim((string) ($row[2] ?? '')),
                // [DITAMBAHKAN] Kolom D = "Product tower" -> divisi IPG/PSG
                'divisi'      => strtoupper(trim((string) ($row[3] ?? ''))),
                'kategori'    => trim((string) ($row[4] ?? '')),
            ];
        }
    }
}