<?php

namespace App\Http\Controllers\Concerns\Email;

use Maatwebsite\Excel\Facades\Excel;

/**
 * Format tanggal/periode Indonesia dan placeholder teks Reminder Invoice.
 *
 * Dipecah dari EmailController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: bulanIndex(), parseTanggalReminder(), formatTanggalIndo(), formatPeriodeIndo(), mostCommon(), applyReminderPlaceholders()
 */
trait FormatsReminderText
{
    private function bulanIndex(string $name): ?int
    {
        $name = strtolower(trim($name));
        $map = [
            'jan'=>1,'januari'=>1,'january'=>1,'feb'=>2,'februari'=>2,'february'=>2,
            'mar'=>3,'maret'=>3,'march'=>3,'apr'=>4,'april'=>4,'mei'=>5,'may'=>5,
            'jun'=>6,'juni'=>6,'june'=>6,'jul'=>7,'juli'=>7,'july'=>7,
            'agu'=>8,'agt'=>8,'aug'=>8,'agustus'=>8,'august'=>8,'sep'=>9,'sept'=>9,'september'=>9,
            'okt'=>10,'oct'=>10,'oktober'=>10,'october'=>10,'nov'=>11,'november'=>11,
            'des'=>12,'dec'=>12,'desember'=>12,'december'=>12,
        ];
        return $map[$name] ?? null;
    }

    private function parseTanggalReminder($value): ?\Carbon\Carbon
    {
        $v = trim((string) $value);
        if ($v === '') return null;

        try {
            // serial number Excel
            if (preg_match('/^\d{5}(\.\d+)?$/', $v)) {
                return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v))->startOfDay();
            }
            // 2027-02-28 / 2027-02-28 00:00:00
            if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $v, $m)) {
                return \Carbon\Carbon::create((int)$m[1], (int)$m[2], (int)$m[3]);
            }
            // 28-Feb-27 / 28 Februari 2027 / 28/Feb/2027
            if (preg_match('/^(\d{1,2})[\s\-\/\.]+([A-Za-z]+)[\s\-\/\.,]+(\d{2,4})/', $v, $m)) {
                $bln = $this->bulanIndex($m[2]);
                if ($bln) {
                    $thn = (int) $m[3];
                    if ($thn < 100) $thn += 2000;
                    return \Carbon\Carbon::create($thn, $bln, (int) $m[1]);
                }
            }
            // 28/02/2027 atau 28-02-2027 (dd/mm/yyyy)
            if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/', $v, $m)) {
                $thn = (int) $m[3];
                if ($thn < 100) $thn += 2000;
                return \Carbon\Carbon::create($thn, (int) $m[2], (int) $m[1]);
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    private function formatTanggalIndo($value): string
    {
        $d = $this->parseTanggalReminder($value);
        if (!$d) return trim((string) $value);
        return $d->day . ' ' . self::BULAN_ID[$d->month] . ' ' . $d->year;
    }

    private function formatPeriodeIndo($value): string
    {
        $v = trim((string) $value);
        if ($v === '') return '';

        // "Agustus 2026" / "Aug-26" / "Aug 2026"
        if (preg_match('/^([A-Za-z]+)[\s\-\/\.]+(\d{2,4})$/', $v, $m) && ($bln = $this->bulanIndex($m[1]))) {
            $thn = (int) $m[2];
            if ($thn < 100) $thn += 2000;
            return self::BULAN_ID[$bln] . ' ' . $thn;
        }
        // tanggal lengkap -> "Bulan Tahun"
        $d = $this->parseTanggalReminder($v);
        if ($d) return self::BULAN_ID[$d->month] . ' ' . $d->year;

        return $v;
    }

    private function mostCommon(array $values): string
    {
        $values = array_values(array_filter(array_map(fn ($x) => trim((string) $x), $values), fn ($x) => $x !== ''));
        if (!$values) return '';
        $counts = array_count_values($values);
        arsort($counts);
        return (string) array_key_first($counts);
    }

    /**
     * Ganti placeholder {{periode_closing}} / {{jatuh_tempo}} dan juga teks lama yang
     * sebelumnya diketik manual ("Periode Agustus 2026", "28 Februari 2027 (batas...")
     * dengan nilai dari Excel (kolom Periode Closing & Jatuh Tempo).
     */
    private function applyReminderPlaceholders(string $text, $reminderRows): string
    {
        if (!$reminderRows || count($reminderRows) === 0) {
            return $text;
        }

        $periode = $this->formatPeriodeIndo($this->mostCommon(collect($reminderRows)->pluck('periode_closing')->all()));
        $jatuhRaw = collect($reminderRows)->map(fn ($r) => data_get($r, 'jatuh_tempo') ?? data_get($r, 'status'))->all();
        $tglCounts = [];
        foreach ($jatuhRaw as $raw) {
            $t = $this->formatTanggalIndo($raw);
            if ($t !== '') $tglCounts[] = $t;
        }
        $jatuh = $this->mostCommon($tglCounts);

        if ($periode !== '') {
            $text = str_replace(['{{periode_closing}}', '{{periode}}'], $periode, $text);
            // teks lama: "Periode Agustus 2026" / "periode Agustus 2026"
            $text = preg_replace(
                '/(periode\s+)(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4}/iu',
                '${1}' . str_replace('$', '\$', $periode),
                $text
            );
        }

        if ($jatuh !== '') {
            $text = str_replace('{{jatuh_tempo}}', $jatuh, $text);
            // teks lama: "28 Februari 2027" yang diikuti "(batas waktu terakhir)"
            $text = preg_replace(
                '/\d{1,2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4}(?=\s*(?:<[^>]*>\s*)*(?:dan\s*)?(?:<[^>]*>\s*)*\(\s*batas)/iu',
                str_replace('$', '\$', $jatuh),
                $text
            );
        }

        return $text;
    }
}
