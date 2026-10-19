<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;

/*
|--------------------------------------------------------------------------
| PembayaranImport
|--------------------------------------------------------------------------
| [DIUBAH] Kolom sekarang dibaca berdasarkan NAMA HEADER (baris yang memuat
| "Company name", "Kode", dan "JUMLAH"), bukan nomor urut kolom lagi.
| Jadi template boleh punya kolom tambahan (mis. "Periode", "Subject") atau
| urutan kolom berubah, dan data tidak akan tertukar.
|
| Penyebab error lama: kolom RINCIAN (teks berisi Case ID dan nominal) terbaca
| sebagai JUMLAH, lalu semua huruf dibuang sehingga tersisa 20 digit angka
| -> "Out of range value for column 'jumlah'".
|
| Kalau header tidak ditemukan, dipakai susunan kolom LAMA (tanpa Periode &
| Subject) supaya file lama tetap bisa diupload.
|--------------------------------------------------------------------------
*/

class PembayaranImport implements ToCollection
{
    protected $kodeUpload;

    // Susunan kolom lama (sebelum ada kolom "Periode" dan "Subject")
    protected const LEGACY_HEADER_INDEX = 4;

    protected const LEGACY_MAP = [
        'company'    => 1,
        'kode'       => 2,
        'norek'      => 3,
        'bank'       => 4,
        'an'         => 5,
        'keterangan' => 7,
        'jumlah'     => 8,
        'total'      => 9,
        'bukti'      => 11,
    ];

    // Batas aman untuk kolom decimal(15,2): maksimal 13 digit sebelum koma
    protected const MAX_NOMINAL = 9999999999999;

    public function __construct($kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    public function collection(Collection $rows)
    {
        [$headerIndex, $map] = $this->detectLayout($rows);

        // [DITAMBAHKAN] Kolom "subject" di tabel pembayaran (isi: kolom Subject di Excel,
        // mis. "APRIL 2026"). Dicek dulu supaya upload tidak error kalau migrasi belum jalan.
        $hasSubjectColumn = Schema::hasColumn('pembayaran', 'subject');

        // Semua baris masuk atau tidak sama sekali (tidak ada data setengah jadi)
        DB::transaction(function () use ($rows, $headerIndex, $map, $hasSubjectColumn) {

            foreach ($rows as $index => $row) {

                // Lewati judul + header
                if ($index <= $headerIndex) {
                    continue;
                }

                // Lewati baris tanpa kode company (baris total =SUM, baris kosong)
                $kode = $this->text($row[$map['kode']] ?? null);

                if ($kode === '') {
                    continue;
                }

                $jumlah = $this->money($row[$map['jumlah']] ?? null);

                $total = isset($map['total'])
                    ? $this->money($row[$map['total']] ?? null)
                    : null;

                // Total berupa rumus yang belum terhitung (mis. "=K6") -> pakai Jumlah
                // dan sebaliknya. Kalau dua-duanya tidak ada nominal, baris dilewati.
                if ($jumlah === null && $total === null) {
                    continue;
                }

                $jumlah = $jumlah ?? $total;
                $total  = $total ?? $jumlah;

                if (abs($jumlah) > self::MAX_NOMINAL || abs($total) > self::MAX_NOMINAL) {
                    throw new \InvalidArgumentException(
                        'Baris Excel ke-' . ($index + 1) . ' (' . $kode . '): nominal tidak wajar ('
                        . $jumlah . '). Cek susunan kolom file: kolom "JUMLAH Rp." harus berisi nominal.'
                    );
                }

                $bukti = isset($map['bukti'])
                    ? $this->text($row[$map['bukti']] ?? null)
                    : '';

                $data = [

                    'kode_upload'      => $this->kodeUpload,
                    'kode_company'     => $kode,
                    'company_name'     => $this->text($row[$map['company']] ?? null),
                    'no_rek'           => $this->text($row[$map['norek']] ?? null),
                    'bank'             => $this->text($row[$map['bank']] ?? null),
                    'atas_nama'        => $this->text($row[$map['an']] ?? null),
                    'keterangan'       => $this->text($row[$map['keterangan']] ?? null),

                    'jumlah'           => $jumlah,
                    'total'            => $total,

                    // Kolom "bukti pembayaran" terakhir (yang terbaru)
                    'bukti_pembayaran' => $bukti !== '' ? $bukti : null,

                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                if ($hasSubjectColumn && isset($map['subject'])) {

                    $subject = $this->text($row[$map['subject']] ?? null);

                    $data['subject'] = $subject !== '' ? $subject : null;
                }

                DB::table('pembayaran')->insert($data);
            }
        });
    }

    /**
     * Cari baris header dan petakan nama kolom -> nomor kolom.
     *
     * @return array [int $headerIndex, array $map]
     */
    protected function detectLayout(Collection $rows): array
    {
        foreach ($rows->take(15) as $index => $row) {

            $map = [];

            foreach ($row as $col => $cell) {

                $key = $this->normalize($cell);

                switch ($key) {
                    case 'companyname':
                        $map['company'] ??= $col;
                        break;
                    case 'kode':
                        $map['kode'] ??= $col;
                        break;
                    case 'subject':
                        $map['subject'] ??= $col;
                        break;
                    case 'norek':
                        $map['norek'] ??= $col;
                        break;
                    case 'bank':
                        $map['bank'] ??= $col;
                        break;
                    case 'an':
                        $map['an'] ??= $col;
                        break;
                    case 'keterangan':
                        $map['keterangan'] ??= $col;
                        break;
                    case 'jumlahrp':
                    case 'jumlah':
                        $map['jumlah'] ??= $col;
                        break;
                    case 'total':
                        $map['total'] ??= $col;
                        break;
                    case 'buktipembayaran':
                        // ada dua kolom "bukti pembayaran": yang paling kanan dipakai
                        $map['bukti'] = $col;
                        break;
                }
            }

            if (isset($map['company'], $map['kode'], $map['jumlah'])) {

                // Kolom yang tidak ada di header -> tetap ada di peta supaya tidak error
                foreach (['norek', 'bank', 'an', 'keterangan'] as $optional) {
                    $map[$optional] ??= 9999;
                }

                return [$index, $map];
            }
        }

        // Header tidak ketemu -> susunan lama
        return [self::LEGACY_HEADER_INDEX, self::LEGACY_MAP];
    }

    protected function normalize($value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
    }

    /**
     * Isi sel -> teks rapi. Angka bulat dari Excel (8630.0) jadi "8630".
     */
    protected function text($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) && floor($value) == $value && abs($value) < 1e15) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }

    /**
     * Isi sel -> nominal (float), atau null kalau kosong / rumus yang belum terhitung.
     * Mengerti format Indonesia: "1.500.000", "Rp 1.500.000,50", angka polos 50000.
     */
    protected function money($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $s = trim((string) $value);

        // Rumus yang tidak terhitung (mis. "=K6")
        if ($s === '' || $s[0] === '=') {
            return null;
        }

        $negative = str_contains($s, '-') || (bool) preg_match('/^\(.*\)$/', $s);

        $s = preg_replace('/[^0-9.,]/', '', $s);

        if ($s === '' || !preg_match('/\d/', $s)) {
            return null;
        }

        $lastDot   = strrpos($s, '.');
        $lastComma = strrpos($s, ',');

        if ($lastDot !== false && $lastComma !== false) {

            // Pemisah desimal = yang paling kanan
            if ($lastComma > $lastDot) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                $s = str_replace(',', '', $s);
            }

        } elseif ($lastComma !== false) {

            // "1.234,5"-nya sudah ditangani di atas; di sini hanya koma
            $after = strlen($s) - $lastComma - 1;

            if (substr_count($s, ',') === 1 && $after !== 3) {
                $s = str_replace(',', '.', $s);   // koma desimal
            } else {
                $s = str_replace(',', '', $s);    // koma ribuan
            }

        } elseif ($lastDot !== false) {

            $after = strlen($s) - $lastDot - 1;

            if (substr_count($s, '.') === 1 && $after !== 3) {
                // titik desimal, biarkan
            } else {
                $s = str_replace('.', '', $s);    // titik ribuan (format Indonesia)
            }
        }

        $number = (float) $s;

        return $negative ? -$number : $number;
    }
}