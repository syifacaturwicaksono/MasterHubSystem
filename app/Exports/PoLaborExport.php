<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PoLaborExport implements FromArray, WithEvents
{
    protected Collection $rows;
    protected float $totalLabor = 0;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;

        $this->totalLabor = (float) $rows->sum(function ($row) {
            $val = str_replace(',', '', $row->labor ?? '');
            return is_numeric($val) ? (float) $val : 0;
        });
    }

    public function array(): array
    {
        $data = [];

        $periode = now()->subMonth()->locale('id')->translatedFormat('F Y');

        // Judul
        $data[] = ['ISD Labor Report'];
        $data[] = ['Periode : ' . $periode];
        $data[] = [''];

        // Header mulai baris 4
        $data[] = [
            'No',
            'Case ID',
            'Case ID Manual',
            'Company Name',
            'Received Date',
            'Warranty Status',
            'Serial No.',
            'Product Tower',
            'Product No.',
            'Product Name',
            'Case Type',
            'Case Status',
            'Problem Desc.',
            'Repair Action',
            'Customer Company',
            'Customer Name',
            'PIC Name',
            'City',
            'Phone No.',
            'Mobile No.',
            'Start Repair Date',
            'TAT Case (received-finish)',
            'Finish Date',
            'Closed Date',
            'Labor',
            'Keterangan',
        ];

        // Data mulai baris 5
        foreach ($this->rows as $index => $row) {

            $rawLabor = trim($row->labor ?? '');

            if ($rawLabor === '' || $rawLabor === null) {
                $labor = '-';
            } else {
                $cleanVal = str_replace(',', '', $rawLabor);
                $labor = (is_numeric($cleanVal) && (float) $cleanVal != 0)
                    ? (float) $cleanVal
                    : '-';
            }

            $data[] = [
                $index + 1,
                $row->case_id ?? '',
                $row->case_id_manual ?? '',
                $row->company_name ?? '',
                $row->received_date ?? '',
                $row->warranty_status ?? '',
                $row->serial_no ?? '',
                $row->product_tower ?? '',
                $row->product_no ?? '',
                $row->product_name ?? '',
                $row->case_type ?? '',
                $row->case_status ?? '',
                $row->problem_desc ?? '',
                $row->repair_action ?? '',
                $row->customer_company ?? '',
                $row->customer_name ?? '',
                $row->pic_name ?? '',
                $row->city ?? '',
                $row->phone_no ?? '',
                $row->mobile_no ?? '',
                $row->start_repair_date ?? '',
                $row->tat_case ?? '',
                $row->finish_date ?? '',
                $row->closed_date ?? '',
                $labor,
                $row->keterangan ?? '',
            ];
        }

        // Baris kosong (26 kolom A-Z)
        $data[] = array_fill(0, 26, '');

        // Baris total
        $totalRow = array_fill(0, 26, '');
        $totalRow[23] = 'TOTAL LABOR'; // Kolom X
        $totalRow[24] = $this->totalLabor; // Kolom Y

        $data[] = $totalRow;

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Merge judul sampai kolom Z
                $sheet->mergeCells('A1:Z1');
                $sheet->mergeCells('A2:Z2');

                // Style judul
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Tinggi baris judul
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // Border tabel
                $sheet->getStyle('A4:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Style header
                $sheet->getStyle('A4:Z4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        
                    ],
                ]);

                $sheet->getRowDimension(4)->setRowHeight(18);

                // Freeze header
                $sheet->freezePane('A5');

                // Lebar kolom manual
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(11);
                $sheet->getColumnDimension('C')->setWidth(11);
                $sheet->getColumnDimension('D')->setWidth(11);
                $sheet->getColumnDimension('E')->setWidth(11);
                $sheet->getColumnDimension('F')->setWidth(11);
                $sheet->getColumnDimension('G')->setWidth(11);
                $sheet->getColumnDimension('H')->setWidth(11);
                $sheet->getColumnDimension('I')->setWidth(11);
                $sheet->getColumnDimension('J')->setWidth(11);
                $sheet->getColumnDimension('K')->setWidth(11);
                $sheet->getColumnDimension('L')->setWidth(11);
                $sheet->getColumnDimension('M')->setWidth(11);
                $sheet->getColumnDimension('N')->setWidth(11);
                $sheet->getColumnDimension('O')->setWidth(11);
                $sheet->getColumnDimension('P')->setWidth(11);
                $sheet->getColumnDimension('Q')->setWidth(11);
                $sheet->getColumnDimension('R')->setWidth(11);
                $sheet->getColumnDimension('S')->setWidth(11);
                $sheet->getColumnDimension('T')->setWidth(11);
                $sheet->getColumnDimension('U')->setWidth(11);
                $sheet->getColumnDimension('V')->setWidth(11);
                $sheet->getColumnDimension('W')->setWidth(11);
                $sheet->getColumnDimension('X')->setWidth(13);
                $sheet->getColumnDimension('Y')->setWidth(17); // [DIUBAH] lebih lebar untuk format Currency
                $sheet->getColumnDimension('Z')->setWidth(18);

                // Format tanggal
                foreach (['E', 'U', 'W', 'X'] as $col) {
                    $sheet->getStyle($col . '5:' . $col . $highestRow)
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                }

                // [DIUBAH] Format Currency (akuntansi) kolom Y: "Rp" rata kiri,
                // angka rata kanan, sehingga semua nominal sejajar rapi.
                // Nilainya tetap angka asli (bisa dijumlah di Excel); sel berisi
                // teks "-" (tanpa Labor) tampil rata kanan.
                $sheet->getStyle('Y5:Y' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* "-"_);_(@_)');

                $sheet->getStyle('Y5:Y' . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Nomor rata tengah
                $sheet->getStyle('A5:A' . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style baris total
                $totalRowNumber = $highestRow;

                $sheet->getStyle('X' . $totalRowNumber . ':Y' . $totalRowNumber)
                    ->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF2CC'],
                        ],
                    ]);

                $sheet->getRowDimension($totalRowNumber)->setRowHeight(24);
            },
        ];
    }
}