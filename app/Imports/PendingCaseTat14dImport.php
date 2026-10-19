<?php

namespace App\Imports;

use App\Models\PendingCaseTat14d;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PendingCaseTat14dImport implements ToCollection
{
    protected $batch;

    public function __construct($batch)
    {
        $this->batch = $batch;
    }

    private function formatDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {

            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            return date('Y-m-d', strtotime($value));

        } catch (\Exception $e) {

            return null;
        }
    }

    public function collection(Collection $rows)
    {
        // Ambil Header
        $header = $rows->first()
            ->map(fn($h) => trim(strtolower($h)))
            ->toArray();

        // Ambil Data
        $rows->skip(1)->each(function ($row) use ($header) {

            if (empty($row[2])) {
                return;
            }

            $data = [];

            foreach ($header as $index => $name) {
                $data[$name] = $row[$index] ?? null;
            }

            PendingCaseTat14d::create([

                'jenis' => 'W002-TAT14D (Pending case 14 days)',
                'kode_upload' => $this->batch,

                'company_name' => $data['company name'] ?? null,

                'aging' => isset($data['aging'])
                    ? round((float)$data['aging'], 1)
                    : null,

                'case_id' => $data['case id'] ?? null,

                'received_date' => $this->formatDate(
                    $data['received date'] ?? null
                ),

                'start_repair_date' => $this->formatDate(
                    $data['start repair date'] ?? null
                ),

                'part_request_date' => $this->formatDate(
                    $data['part request date'] ?? null
                ),

                'part_order_date' => $this->formatDate(
                    $data['part order date'] ?? null
                ),

                'eta_date' => $this->formatDate(
                    $data['eta date'] ?? null
                ),

                'part_in_date' => $this->formatDate(
                    $data['part in date'] ?? null
                ),

                'part_in_status' => $data['partinstatus'] ?? null,

                'so_no' => $data['so no.'] ?? null,

                'hp_part_no' => $data['hp part no.'] ?? null,

                'case_status' => $data['case status'] ?? null,

                'product_tower' => $data['product tower'] ?? null,

                'product_type' => $data['product type'] ?? null,

                'ce_name' => $data['ce name'] ?? null,

            ]);
        });
    }
}