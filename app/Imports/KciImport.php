<?php

namespace App\Imports;

use App\Models\Kci;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class KciImport implements ToCollection
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
        // Ambil header
        $header = $rows->first()
            ->map(fn($h) => trim(strtolower($h)))
            ->toArray();
            

        // Ambil isi data
        $rows->skip(1)->each(function ($row) use ($header) {

            if (empty($row[0])) {
                return;
            }

            $data = [];

            foreach ($header as $index => $name) {
                $data[$name] = $row[$index] ?? null;
            }

            Kci::create([

    
                'jenis_monitoring' => 'W004-KCI',
                'kode_upload' => $this->kodeUpload,

                'case_id' => $data['case id'] ?? null,

                'count' => isset($data['count']) && $data['count'] !== ''
                    ? (int) $data['count']
                    : null,

                'company_name' => $data['company name'] ?? null,

                'aging' => isset($data['aging']) && $data['aging'] !== ''
                    ? round((float) $data['aging'], 1)
                    : null,

                'customer_name' => $data['customer name'] ?? null,

                'customer_company_hierarchy' =>
                    $data['customer company hierarchy - customer company'] ?? null,

                'case_status' => $data['case status'] ?? null,

                'ce_name' => $data['ce name'] ?? null,

                'company_city' => $data['company city'] ?? null,

]);
        });
    }
}