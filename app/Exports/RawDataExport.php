<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RawDataExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Case ID',
            'Received Date',
            'Start Repair',
            'Company Name',
            'Case Status',
            'Company City',
            'Product No',
            'Product Name',
            'PN Code',
            'Part Request',
            'SO No',
            'ETA Date',
            'Part In Date',
        ];
    }
}