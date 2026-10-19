<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| CompanyGroup  (simpan di: app/Support/CompanyGroup.php)
|--------------------------------------------------------------------------
| Menggabungkan beberapa cabang PO Labor (APO) yang kode Case ID-nya beda
| tapi satu perusahaan (mis. ELS COMPUTER Yogya / Solo / Purwokerto) menjadi
| SATU baris di BC Email dengan satu kode gabungan.
|
| Aturan: company yang namanya DIAWALI "prefix" di bawah otomatis masuk grup.
| Case ID di data tidak diubah; hanya kode & nama yang tampil/dipilih yang
| disatukan. Tambah grup lain cukup menambah satu baris di GROUPS.
|--------------------------------------------------------------------------
*/

class CompanyGroup
{
    /** kode gabungan (3 digit) => nama tampil & awalan nama company di data */
    public const GROUPS = [
        '630' => ['name' => 'ELS COMPUTER', 'prefix' => 'ELS COMPUTER'],
    ];

    /** Nama tampil grup kalau $kode adalah kode gabungan, selain itu null. */
    public static function groupName(?string $kode): ?string
    {
        $kode = substr(trim((string) $kode), 0, 3);

        return self::GROUPS[$kode]['name'] ?? null;
    }

    /** Kode gabungan untuk sebuah nama company (diawali prefix grup), atau null. */
    public static function matchGroup(?string $companyName): ?string
    {
        $name = trim((string) $companyName);

        foreach (self::GROUPS as $kode => $group) {
            if (stripos($name, $group['prefix']) === 0) {
                return (string) $kode;
            }
        }

        return null;
    }

    /**
     * Daftar awalan Case ID (3 digit) yang tercakup oleh $kode.
     * Kode biasa -> [kode]; kode gabungan -> semua awalan Case ID milik
     * company yang namanya diawali prefix grup (+ kode gabungan itu sendiri).
     */
    public static function apoCodes(?string $kode): array
    {
        $kode3 = substr(trim((string) $kode), 0, 3);

        if (!isset(self::GROUPS[$kode3])) {
            return [$kode3];
        }

        $codes = DB::table('apo')
            ->selectRaw('DISTINCT LEFT(case_id,3) as c')
            ->where('company_name', 'like', self::GROUPS[$kode3]['prefix'] . '%')
            ->get()
            ->pluck('c')
            ->map(fn ($c) => trim((string) $c))
            ->filter(fn ($c) => $c !== '')
            ->push($kode3)
            ->unique()
            ->values()
            ->all();

        return $codes;
    }

    /**
     * Untuk listing BC Email PO Labor: baris-baris (kode_company, company_name)
     * milik grup disatukan jadi satu baris berkode gabungan.
     */
    public static function collapseApoListing(Collection $rows): Collection
    {
        // Kumpulkan dulu awalan Case ID asli tiap grup (633, 635, 665, ...) dari
        // baris listing itu sendiri, supaya total case & modal memakai data yang
        // sama persis dengan yang tampil di daftar.
        $members = [];

        foreach ($rows as $row) {
            $group = self::matchGroup($row->company_name ?? '');

            if ($group !== null) {
                $members[$group][] = substr(trim((string) $row->kode_company), 0, 3);
            }
        }

        return $rows
            ->map(function ($row) use ($members) {
                $group = self::matchGroup($row->company_name ?? '');

                if ($group !== null) {
                    $row->apo_codes = array_values(array_unique(array_filter(
                        array_merge($members[$group] ?? [], [$group]),
                        fn ($c) => $c !== ''
                    )));
                    $row->kode_company = $group;
                    $row->company_name = self::GROUPS[$group]['name'];
                }

                return $row;
            })
            ->unique(fn ($row) => $row->kode_company . '|' . $row->company_name)
            ->sortBy('kode_company')
            ->values();
    }
}