<?php

namespace App\Imports;

use App\Models\Kla;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Carbon;

class KlaImport implements ToCollection
{
    protected $kodeUpload;

    public function __construct($kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    /**
     * Format tanggal/timestamp dari Excel atau String
     */
    private function formatDate($value, $includeTime = false)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $format = $includeTime ? 'Y-m-d H:i:s' : 'Y-m-d';

            // 1. Jika formatnya angka (Excel Date Serial Number)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format($format);
            }

            // Clean string
            $valueStr = trim((string) $value);

            // 2. Jika format string Indonesia '24/07/2026 8:48:54' atau '24/07/2026'
            if (str_contains($valueStr, '/')) {
                // Ganti slash dengan dash agar Carbon/strtotime membacanya sebagai d-m-Y (UK/Indo standard)
                $valueStr = str_replace('/', '-', $valueStr);
            }

            return Carbon::parse($valueStr)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Ambil header dan bersihkan karakter tersembunyi/spasi
        $header = $rows->first()
            ->map(fn($h) => trim(strtolower((string) $h)))
            ->toArray();

        // Ambil isi data
        $rows->skip(1)->each(function ($row) use ($header) {
            // Cek jika baris kosong
            if (!isset($row[0]) || trim((string) $row[0]) === '') {
                return;
            }

            $data = [];
            foreach ($header as $index => $name) {
                $data[$name] = $row[$index] ?? null;
            }

            Kla::create([
                'jenis_monitoring'  => $data['jenis_monitoring'] ?? 'D001-KLA',
                'kode_upload'       => $this->kodeUpload,
                
                // Gunakan true agar jam, menit, detik ikut tersimpan di 'timestamp'
                'timestamp'         => $this->formatDate($data['timestamp'] ?? null, true),
                
                'case_id'           => $data['nomor cof id'] ?? null,
                'company_name'      => $data['kantor cabang'] ?? null,
                'teknisi'           => $data['nama teknisi'] ?? null,
                'invoice'        => isset($data['nominal pembayaran invoice']) && $data['nominal pembayaran invoice'] !== ''
                                        ? (float) str_replace(',', '', $data['nominal pembayaran invoice'])
                                        : null,
                'upload'            => $data['upload file cof'] ?? null,
                'transaksi'         => $data['foto transaksi'] ?? null,
                'keterangan'        => $data['keterangan'] ?? null,
                
                // Hanya simpan Tanggal (Y-m-d)
                'tanggal_transaksi' => $this->formatDate($data['tanggal transaksi'] ?? null, false),
                'pembayaran' => $data['pembayaran via qris/transfer?'] ?? null,
            ]);
        });
    }
}