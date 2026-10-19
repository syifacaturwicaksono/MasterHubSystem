<?php

namespace App\Imports;

use App\Models\WipData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WipImport implements ToCollection, WithHeadingRow
{
    protected $kodeUpload;

    public function __construct($kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    /**
     * Header berada di baris ke-4 sheet Parts.
     */
    public function headingRow(): int
    {
        return 4;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            // Skip jika Case ID kosong
            if (empty($row['case_id'])) {
                continue;
            }

            $finishDate = !empty($row['finish_date'])
                ? Carbon::parse($row['finish_date'])
                : null;

            $partInDate = !empty($row['part_in_date'])
                ? Carbon::parse($row['part_in_date'])
                : null;

            // Warranty Status harus In warranty
            $statusWarranty = trim($row['warranty_status'] ?? '');

            if (
                strcasecmp($statusWarranty, 'In Warranty') != 0 &&
                strcasecmp($statusWarranty, 'Care Pack') != 0
            ) {
                continue;
            }

            // Case Status
            $status = trim($row['case_status'] ?? '');

            if (
                stripos($status, 'Close Repair') === false &&
                stripos($status, 'Close Cancel') === false &&
                stripos($status, 'Finish') === false
            ) {
                continue;
            }
            // HP Part No, jika kosong gunakan Vendor Part No
            $partNo = !empty($row['hp_part_no'])
                ? $row['hp_part_no']
                : ($row['vendor_part_no'] ?? null);

            if (empty($partNo)) {
                continue;
            }

            // Part Return Status
            $partReturn = trim($row['part_return_status'] ?? '');

            // Skip jika nilainya "No Part Return"
            if (strcasecmp($partReturn, 'No Part Return') === 0) {
                continue;
            }

            // SO No wajib ada
            if (empty($row['so_no'])) {
                continue;
            }

            // AWB Return harus kosong
            if (!empty($row['awb_no_part_return'])) {
                continue;
            }

            // Part In Date wajib ada
            if (!$partInDate) {
                continue;
            }

            $aging = $partInDate->diffInDays(Carbon::today());


            WipData::create([
                'kode_upload'        => $this->kodeUpload,
                'jenis_monitoring'   => 'W001-WIP',

                'case_id_manual'     => $row['case_id'],
                'company_name'       => $row['company_name'] ?? null,
                'finish_date'        => $finishDate,
                'case_status'        => $row['case_status'],
                'hp_part_no'         => $partNo,
                'so_no'              => $row['so_no'],
                'awb_no_part_return' => $row['awb_no_part_return'],
                'part_in_date'       => $partInDate,
                'aging'              => $aging,
                'part_return' => $partReturn,
            ]);
        }
    }
}
