<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| BroadcastScheduler
|--------------------------------------------------------------------------
| ★★★ BARU: penjadwal GLOBAL yang dipakai bareng oleh BroadcastController
| (Broadcast All) dan EmailController (BC Email jenis Pembayaran/Reminder
| Invoice/PO Labor), supaya kalau ada 2 pengiriman batch yang di-klik
| hampir bersamaan, jadwalnya TIDAK numpuk/tabrakan di jam yang sama.
|
| Cara kerja: satu baris di tabel `broadcast_queue_settings` menyimpan
| `next_available_at` — "jam berapa slot pengiriman batch berikutnya
| boleh mulai". Setiap kali ada pengiriman baru mau dijadwalkan:
| 1. Baca next_available_at (kalau kosong/sudah lewat, dianggap "sekarang").
| 2. Pengiriman baru ini mulai dari situ (bukan dari sekarang), kalau
|    ternyata masih ada antrean pengiriman lain yang belum selesai jadwalnya.
| 3. Reservasi slot itu langsung dimajukan sejauh estimasi total durasi
|    pengiriman baru ini, supaya pengiriman BERIKUTNYA (siapapun yang
|    klik kirim setelah ini) otomatis mengantre lagi di belakangnya.
|
| Pakai DB transaction + lockForUpdate() supaya aman kalau 2 orang klik
| kirim di detik yang nyaris sama (race condition).
|--------------------------------------------------------------------------
*/

class BroadcastScheduler
{
    /**
     * Ambil setting batch_size & delay_minutes yang tersimpan (dipakai untuk
     * pengiriman BC Email jenis Pembayaran/Reminder Invoice/PO Labor).
     */
    public static function getSettings(): array
    {
        $row = DB::table('broadcast_queue_settings')->find(1);

        return [
            'batch_size'    => $row->batch_size ?? 30,
            'delay_minutes' => $row->delay_minutes ?? 60,
        ];
    }

    /**
     * Reservasi slot jadwal global. Panggil ini SEKALI per proses dispatch
     * (satu kali per klik tombol kirim), dapatkan $startAt, lalu pakai
     * $startAt sebagai basis buat menghitung delay tiap batch (bukan now()
     * langsung), supaya kalau ternyata masih ada antrean sebelumnya, batch
     * baru ini otomatis mulai setelah antrean itu longgar.
     *
     * @param int $totalBatches      total batch yang akan dijadwalkan pengiriman ini
     * @param int $delayMinutesEach  jeda antar batch (menit) untuk pengiriman ini
     */
    public static function reserveSlot(int $totalBatches, int $delayMinutesEach): Carbon
    {
        return DB::transaction(function () use ($totalBatches, $delayMinutesEach) {

            // Kunci baris ini supaya kalau ada 2 request datang bersamaan,
            // yang kedua nunggu sampai yang pertama selesai update dulu.
            $row = DB::table('broadcast_queue_settings')
                ->lockForUpdate()
                ->find(1);

            $now = now();

            $nextAvailable = $row && $row->next_available_at
                ? Carbon::parse($row->next_available_at)
                : $now;

            // Kalau jadwal yang tersimpan sudah lewat (misal antrean sebelumnya
            // sudah pasti kelar), mulai dari sekarang aja — bukan dari masa lalu.
            $startAt = $nextAvailable->greaterThan($now) ? $nextAvailable : $now;

            // Reservasi slot berikutnya: majukan next_available_at sejauh estimasi
            // total durasi pengiriman INI (jumlah batch x jeda per batch), dihitung
            // dari $startAt (bukan dari sekarang), supaya pengiriman berikutnya
            // otomatis mengantre di belakang pengiriman ini.
            $reservedUntil = $startAt->copy()->addMinutes($totalBatches * $delayMinutesEach);

            DB::table('broadcast_queue_settings')
                ->where('id', 1)
                ->update([
                    'next_available_at' => $reservedUntil,
                    'updated_at'        => $now,
                ]);

            return $startAt;
        });
    }
}