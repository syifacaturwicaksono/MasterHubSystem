<?php

namespace App\Imports;

use App\Models\MprData;
use App\Services\CompanyMapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;


class MprImport implements ToCollection, WithCalculatedFormulas
{
    protected $kodeUpload;

    public function __construct($kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    private function excelFormat($value, $type = 'number')
{
    if ($value === null) {
        return null;
    }

    // kalau string (#N/A dll)
    if (is_string($value)) {
        return trim($value);
    }

    if ($type == 'percent') {
        return round($value * 100) . '%';
    }

    return number_format((float)$value, 2, ',', '.');
}

    private function formatDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {

            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return date('Y-m-d', strtotime($value));

        } catch (\Exception $e) {

            return null;
        }
    }

   public function collection(Collection $rows)
{
   $rows->skip(1)->each(function ($row) {

    if ($row->filter()->isEmpty()) {
        return;
    }

    // [DIUBAH] Deteksi otomatis format baru vs lama.
    // Format baru: kolom pertama ("Code") berisi angka, Company Name geser ke kolom ke-2.
    // Format lama: kolom pertama langsung Company Name (bukan angka).
    $hasCodeColumn = isset($row[0]) && trim((string) $row[0]) !== '' && is_numeric(trim((string) $row[0]));

    if ($hasCodeColumn) {
        $kodeCompanyFromExcel = trim((string) $row[0]);
        $companyName = strtoupper(trim((string) ($row[1] ?? '')));
        $offset = 1; // semua kolom data (Case IPG dst) geser 1 ke kanan
    } else {
        $kodeCompanyFromExcel = null;
        $companyName = strtoupper(trim((string) ($row[0] ?? '')));
        $offset = 0;
    }

    // Berhenti ketika menemukan baris Rata-rata
    if (str_contains($companyName, 'RATA')) {
        return false;
    }

    // [DIUBAH] Prioritaskan kode dari kolom "Code" langsung (lebih akurat).
    // Kalau file lama (tidak ada kolom Code), fallback ke mapping dari sheet Export seperti sebelumnya.
    $kodeCompany = $kodeCompanyFromExcel ?? (CompanyMapping::$mapping[$companyName] ?? null);

    // Jika tidak ada kode company sama sekali, lewati
    if (is_null($kodeCompany)) {
        return;
    }

    MprData::create([
    'jenis_monitoring' => 'MPR',
    'kode_upload'      => $this->kodeUpload,
    'kode_company'     => $kodeCompany,
    'company_name'     => $companyName,

    'case_ipg'         => $this->excelFormat($row[1 + $offset]),
    'case_psg'         => $this->excelFormat($row[2 + $offset]),
    'total_case'       => $this->excelFormat($row[3 + $offset]),
    'tat'              => $this->excelFormat($row[4 + $offset]),
    'tat_wo_bo'        => $this->excelFormat($row[5 + $offset]),
    'ola'              => $this->excelFormat($row[6 + $offset]),
    'rrp'              => $row[7 + $offset] ?? null,
    'rrp_percent'      => $this->excelFormat($row[8 + $offset], 'percent'),
    'wip'              => is_numeric($row[9 + $offset]) ? $this->excelFormat($row[9 + $offset]) : $row[9 + $offset],
    'unupdate_wip'     => is_numeric($row[10 + $offset]) ? $this->excelFormat($row[10 + $offset]) : $row[10 + $offset],
    'latest_aging'     => is_numeric($row[11 + $offset]) ? $this->excelFormat($row[11 + $offset]) : $row[11 + $offset],
    'catatan'          => $row[12 + $offset] ?? null,
]);
});
}

private function number($value)
{
    if ($value === null) {
        return null;
    }

    $value = trim((string)$value);

    // kosong
    if ($value === '') {
        return null;
    }

    // Semua variasi #N/A
    if (stripos($value, '#N/A') !== false) {
        return null;
    }

    if (stripos($value, 'N/A') !== false) {
        return null;
    }

    if ($value === '-') {
        return null;
    }

    // persen
    $value = str_replace('%', '', $value);

    // koma menjadi titik
    $value = str_replace(',', '.', $value);

    // kalau bukan angka
    if (!is_numeric($value)) {
        return null;
    }

    return $value;
}
}