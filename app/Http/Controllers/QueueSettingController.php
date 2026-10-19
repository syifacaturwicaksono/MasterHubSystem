<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

/*
|--------------------------------------------------------------------------
| QueueSettingController
|--------------------------------------------------------------------------
| Halaman admin untuk mengatur timer batching pengiriman BC Email
| (Pembayaran / Reminder Invoice / PO Labor / MPR) — satu timer dipakai
| bersama: jumlah email per batch dan jeda antar batch.
|--------------------------------------------------------------------------
*/

class QueueSettingController extends Controller
{
    // Perintah untuk menjalankan Python. Kalau di Laragon kamu error "python not found",
    // ganti jadi 'py' atau path lengkap, misal 'C:\\Python312\\python.exe'
    private const PYTHON = 'C:\Users\HPS\AppData\Local\Programs\Python\Python312\python.exe';

    public function edit()
    {
        $setting = DB::table('broadcast_queue_settings')->find(1);

        return view('queue_setting', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'batch_size'    => 'required|integer|min:1|max:1000',
            'delay_minutes' => 'required|integer|min:1|max:1440', // maks 24 jam
        ]);

        DB::table('broadcast_queue_settings')
            ->where('id', 1)
            ->update([
                'batch_size'    => $request->batch_size,
                'delay_minutes' => $request->delay_minutes,
                'updated_at'    => now(),
            ]);

        return back()->with('success', 'Setting timer berhasil disimpan.');
    }

    // [DITAMBAHKAN] Bersihkan reservasi jadwal global (next_available_at) —
    // dipakai kalau ada reservasi "numpuk" dari tes-tes kirim sebelumnya yang
    // gak sesuai kondisi asli (misal isinya cuma dikit tapi jadwalnya jauh
    // ke depan), supaya pengiriman berikutnya mulai dari sekarang lagi.
    public function resetQueue()
    {
        DB::table('broadcast_queue_settings')
            ->where('id', 1)
            ->update([
                'next_available_at' => null,
                'updated_at'        => now(),
            ]);

        return back()->with('success', 'Antrean jadwal pengiriman berhasil direset. Pengiriman berikutnya akan mulai dari sekarang.');
    }

    // [DITAMBAHKAN] Simulasi jadwal — hitungannya dikerjakan script Python.
    // Hanya menghitung dan menampilkan; tidak mengirim email dan tidak menyimpan apa pun.
    public function preview(Request $request)
    {
        $request->validate([
            'total_email'   => 'required|integer|min:1|max:100000',
            'batch_size'    => 'required|integer|min:1|max:1000',
            'delay_minutes' => 'required|integer|min:1|max:1440',
        ]);

        $setting = DB::table('broadcast_queue_settings')->find(1);

        // Kalau masih ada antrean yang jalan, simulasi mulai dari slot berikutnya
        // (sama seperti yang akan dilakukan saat kirim sungguhan). Kalau tidak, dari sekarang.
        $slot  = $setting->next_available_at ?? null;
        $mulai = ($slot && Carbon::parse($slot)->isFuture()) ? Carbon::parse($slot) : now();

        // Sama seperti mengetik di terminal:
        // python hitung_jadwal.py --total 120 --batch 50 --delay 30 --mulai "2026-10-01 09:00"
        $process = new Process([
            self::PYTHON,
            base_path('scripts/hitung_jadwal.py'),
            '--total', (string) $request->total_email,
            '--batch', (string) $request->batch_size,
            '--delay', (string) $request->delay_minutes,
            '--mulai', $mulai->format('Y-m-d H:i'),
        ]);
        $process->setTimeout(10);
        $process->run();

        if (! $process->isSuccessful()) {
            return response()->json([
                'error' => 'Script Python gagal: ' . trim($process->getErrorOutput()),
            ], 500);
        }

        // Output Python (JSON string) diubah jadi array lalu dikirim ke browser
        return response()->json(json_decode($process->getOutput(), true));
    }
}