<?php

namespace App\Imports;

use App\Services\CompanyMapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MprExportImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Kosongkan mapping setiap upload
        CompanyMapping::$mapping = [];

        foreach ($rows as $index => $row) {

            // Lewati header
            if ($index == 0) {
                continue;
            }

            // Pastikan kolom ada
            if (!isset($row[2]) || !isset($row[16])) {
                continue;
            }

            $caseId = trim((string) $row[2]);
            $partner = strtoupper(trim((string) $row[16]));

            // Lewati jika kosong
            if ($caseId === '' || $partner === '') {
                continue;
            }

            // Ambil hanya angka dari Case ID
            $caseId = preg_replace('/[^0-9]/', '', $caseId);

            // Harus minimal 3 digit
            if (strlen($caseId) < 3) {
                continue;
            }

            // Ambil 3 digit depan
            $kodeCompany = substr($caseId, 0, 3);

            // Simpan ke mapping
            CompanyMapping::$mapping[$partner] = $kodeCompany;
        }

        // DEBUG
        // dd(CompanyMapping::$mapping);
    }
}