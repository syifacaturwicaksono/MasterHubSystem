<?php

namespace App\Imports;

use App\Models\PendingCaseTat5d;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PendingCaseTat5dImport implements ToCollection
{
    protected $kodeUpload;

    public function __construct($kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    private function formatDate($value)
{
    if (empty($value)) {
        return null;
    }

    try {

        // Jika format Excel (angka)
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)
                ->format('Y-m-d');
        }

        // Jika format text
        return date('Y-m-d', strtotime($value));

    } catch (\Exception $e) {
        return null;
    }
}

    public function collection(Collection $rows)
    {
        $rows->skip(1)->each(function ($row) {

            if (empty($row[0])) {
                return;
            }

            PendingCaseTat5d::create([

    'jenis_monitoring' => 'W003-TAT5D (Pending case 5 days)',
    'kode_upload' => $this->kodeUpload,

    'case_id' => $row[0] ?? null,

    'received_date' => $this->formatDate($row[2] ?? null),

    'start_repair_date' => $this->formatDate($row[3] ?? null),

    'company_name' => $row[4] ?? null,

    'aging' => is_numeric($row[5] ?? null)
        ? round((float)$row[5],1)
        : null,

    'case_status' => $row[8] ?? null,

    'ce_name' => $row[9] ?? null,

    'company_city' => $row[10] ?? null,

    'part_name' => $row[11] ?? null,

    'hp_part_no' => $row[12] ?? null,

    'part_request_date' => $this->formatDate($row[13] ?? null),

    'so_no' => $row[14] ?? null,

    'eta_date' => $this->formatDate($row[15] ?? null),

    'part_in_date' => $this->formatDate($row[16] ?? null),

    'product_no' => $row[17] ?? null,

    'product_name' => $row[18] ?? null,

]);
        });
    }

}