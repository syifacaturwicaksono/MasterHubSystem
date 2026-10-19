<?php

namespace App\Imports;

use App\Models\ReminderInvoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ReminderInvoiceImport implements ToModel, WithHeadingRow
{
    protected $batch;

    public function __construct($batch)
    {
        $this->batch = $batch;
    }

    /**
     * Konversi berbagai format tanggal menjadi Y-m-d
     */
    private function formatTanggal($value)
    {
        if (empty($value)) {
            return null;
        }

        // Jika format tanggal Excel (serial number)
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        // Ubah nama bulan Indonesia menjadi Inggris
        $bulan = [
            'Januari'   => 'January',
            'Februari'  => 'February',
            'Maret'     => 'March',
            'April'     => 'April',
            'Mei'       => 'May',
            'Juni'      => 'June',
            'Juli'      => 'July',
            'Agustus'   => 'August',
            'September' => 'September',
            'Oktober'   => 'October',
            'November'  => 'November',
            'Desember'  => 'December',
        ];

        $value = trim($value);
        $value = strtr($value, $bulan);

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        return new ReminderInvoice([
            'jenis_monitoring'   => 'M002-Reminder Invoice',
            'kode_upload'        => $this->batch,

            'kode_company'       => $row['kode'] ?? null,
            'company_name'       => $row['company_name'] ?? null,
            'partner'            => $row['partner'] ?? null,
            'periode_closing'    => $row['periode_closing'] ?? null,
            'srf_erf'            => $row['srferf'] ?? null,
            'invoice_no'         => $row['invoice_no'] ?? null,
            'case'               => $row['case'] ?? null,
            'kekurangan_dokumen' => $row['kekurangan_dokumen'] ?? null,
            'total'              => $row['total'] ?? null,

            'jatuh_tempo'             => $this->formatTanggal($row['jatuh_tempo'] ?? null),
        ]);
    }
}