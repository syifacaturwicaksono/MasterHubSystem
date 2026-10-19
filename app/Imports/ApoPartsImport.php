<?php

namespace App\Imports;

use App\Services\PartCategoryResolver;
use App\Services\PartLaborMapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/*
|--------------------------------------------------------------------------
| ApoPartsImport
|--------------------------------------------------------------------------
| ★★★ DIUBAH: baca sheet "Parts" dari file PO Labor, tentukan Labor per Case ID,
| lalu simpan hasilnya ke PartLaborMapping::$mapping supaya bisa dipakai ApoImport
| belakangan (sheet "Parts" HARUS diproses lebih dulu — urutannya diatur di
| MultiSheetImport::sheets()).
|
| Sekarang terima $caseOverrides (array case_id => part_no) yang berasal dari
| popup review di halaman Upload Sheet — kalau user sudah eksplisit memilih
| part tertentu untuk 1 case (apapun statusnya, bukan cuma "Defective"), part
| itu yang dipakai PERSIS, tidak dihitung ulang otomatis.
|
| Kalau TIDAK ada override untuk 1 case (mis. request lama tanpa popup review,
| atau override kosong), fallback ke aturan lama sebagai jaga-jaga:
| 1. Cuma part dengan "Part return status" PERSIS "Defective" yang dihitung.
| 2. [DIUBAH] Kalau lebih dari 1 part "Defective", ambil yang Part Request
|    Date-nya PALING AWAL (part yang PERTAMA di-order) — bukan lagi yang
|    kategorinya harga paling besar.
| 3. Kalau gak ada part "Defective" sama sekali, Labor-nya kosong.
|--------------------------------------------------------------------------
*/

class ApoPartsImport implements ToCollection, WithHeadingRow
{
    /** @var array<string,string> case_id => part_no yang dipilih user di popup review */
    protected array $caseOverrides;

    public function __construct(array $caseOverrides = [])
    {
        $this->caseOverrides = $caseOverrides;
    }

    public function headingRow(): int
    {
        return 4;
    }

    // [DITAMBAHKAN] Ubah Part Request Date (serial Excel/string tanggal)
    // jadi angka yang bisa dibandingkan. Kosong/gak valid dianggap paling
    // akhir (INF), supaya part yang tanggalnya jelas selalu menang duluan
    // dibanding yang datanya kosong.
    private function partRequestDateSortValue($value): float
    {
        if (empty($value)) {
            return INF;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        try {
            // Serial Excel (numerik) sudah ditangani di atas; string seperti
            // "29 Apr 2026 14:22:10" atau "29/04/2026" diparse Carbon.
            return (float) \Carbon\Carbon::parse(trim((string) $value))->timestamp;
        } catch (\Throwable $e) {
            return INF;
        }
    }

    public function collection(Collection $rows)
    {
        // Reset mapping tiap upload baru
        PartLaborMapping::$mapping = [];

        // 1. Kelompokkan SEMUA part (apapun statusnya) per Case ID — dibutuhkan
        //    supaya override bisa nunjuk ke part manapun, bukan cuma yang Defective.
        $allByCase = [];

        // [DITAMBAHKAN] Case dengan status Cancel (termasuk kode 511 / SPP) tidak
        // dapat Labor, sama seperti case Cancel lainnya.
        $cancelCases = [];

        foreach ($rows as $rowIndex => $row) {

            $caseId = trim((string) ($row['case_id'] ?? ''));
            // [DIUBAH] Kalau HP part no kosong, pakai Vendor part no supaya part tetap terbaca.
            $partNo = trim((string) ($row['hp_part_no'] ?? ''));

            if ($partNo === '') {
                $partNo = trim((string) (
                    $row['vendor_part_no']
                    ?? $row['vendor_part_number']
                    ?? $row['vendor_part']
                    ?? ''
                ));
            }

            if ($caseId === '') {
                continue;
            }

            if (str_contains(strtolower((string) ($row['case_status'] ?? '')), 'cancel')) {
                $cancelCases[$caseId] = true;
            }

            if ($partNo === '') {
                continue;
            }

            $allByCase[$caseId][] = [
                'part_no'       => $partNo,
                'part_name'     => trim((string) ($row['part_name'] ?? '')),
                'product_tower' => trim((string) ($row['product_tower'] ?? '')),
                'status'        => strtolower(trim((string) ($row['part_return_status'] ?? ''))),
                // [DITAMBAHKAN] dipakai buat nentuin part yang PERTAMA
                // di-order (bukan lagi berdasarkan harga terbesar)
                'part_request_date' => $row['part_request_date'] ?? null,
                // [DITAMBAHKAN] urutan baris di Excel (0 = paling atas). Di sheet Parts,
                // baris ATAS = order terbaru, baris BAWAH = order PERTAMA.
                'row_index' => (int) $rowIndex,
            ];
        }

        // 2. Untuk tiap Case ID, tentukan part pemenangnya
        foreach ($allByCase as $caseId => $candidates) {

            // [DITAMBAHKAN] Cancel -> tidak ada mapping Labor sama sekali
            if (isset($cancelCases[$caseId])) {
                continue;
            }

            // [DITAMBAHKAN] Kalau user sudah pilih manual lewat popup review, pakai
            // PERSIS part itu — cari di antara semua part case ini (apapun statusnya).
            if (isset($this->caseOverrides[$caseId])) {

                $chosenPartNo = $this->caseOverrides[$caseId];
                $chosen = collect($candidates)->firstWhere('part_no', $chosenPartNo);

                if ($chosen) {

                    $resolved = PartCategoryResolver::resolve(
                        $chosen['part_no'],
                        $chosen['part_name'],
                        $chosen['product_tower']
                    );

                    PartLaborMapping::$mapping[$caseId] = [
                        'labor'     => $resolved['price'],
                        'part_no'   => $chosen['part_no'],
                        'part_name' => $chosen['part_name'],
                        'category'  => $resolved['category_name'],
                    ];

                    continue; // lanjut ke case berikutnya, skip logic fallback di bawah
                }
                // Kalau part_no override-nya gak ketemu di data (kasus aneh/rusak),
                // jatuh ke fallback otomatis di bawah, biar tetap ada hasil.
            }

            // [FALLBACK] Logic lama: cuma part "Defective".
            // [DIUBAH] Sebelumnya ambil yang kategorinya harga PALING BESAR.
            // Sekarang ambil yang Part Request Date-nya PALING AWAL (part
            // PERTAMA yang di-order untuk case ini).
            $defectives = array_filter($candidates, fn($c) => $c['status'] === 'defective');

            $winner = null;
            $winnerDateValue = INF;

            foreach ($defectives as $candidate) {

                $dateValue = $this->partRequestDateSortValue(
                    $candidate['part_request_date']
                );

                // [DIUBAH] Tanggal sama / tidak terbaca -> baris Excel yang lebih BAWAH
                // dianggap lebih awal di-order, jadi pakai <= (kandidat diiterasi atas -> bawah).
                if ($winner === null || $dateValue <= $winnerDateValue) {

                    $resolved = PartCategoryResolver::resolve(
                        $candidate['part_no'],
                        $candidate['part_name'],
                        $candidate['product_tower']
                    );

                    $winner = [
                        'labor'     => $resolved['price'],
                        'part_no'   => $candidate['part_no'],
                        'part_name' => $candidate['part_name'],
                        'category'  => $resolved['category_name'],
                    ];

                    $winnerDateValue = $dateValue;
                }
            }

            if ($winner !== null) {
                PartLaborMapping::$mapping[$caseId] = $winner;
            }
        }
    }
}