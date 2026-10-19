"""
hitung_jadwal.py
----------------
Menghitung jadwal pengiriman email per batch.

Cara pakai (dari terminal / dipanggil Laravel):
    python hitung_jadwal.py --total 120 --batch 50 --delay 30 --mulai "2026-10-01 09:00"

Hasilnya dicetak sebagai JSON, supaya gampang dibaca oleh Laravel.
"""

import argparse
import json
import math
from datetime import datetime, timedelta


def hitung_jadwal(total_email, batch_size, delay_menit, mulai):
    """Mengembalikan daftar batch beserta jam kirimnya."""
    # math.ceil = pembulatan ke atas. 120 email / 50 per batch = 2.4 -> 3 batch
    jumlah_batch = math.ceil(total_email / batch_size)

    jadwal = []
    sisa = total_email

    for nomor in range(1, jumlah_batch + 1):
        # Batch terakhir bisa isinya kurang dari batch_size, makanya pakai min()
        isi = min(batch_size, sisa)

        # Batch 1 langsung jalan di waktu "mulai", batch 2 setelah 1x jeda, dst.
        waktu_kirim = mulai + timedelta(minutes=delay_menit * (nomor - 1))

        jadwal.append({
            "batch": nomor,
            "jumlah_email": isi,
            "waktu_kirim": waktu_kirim.strftime("%Y-%m-%d %H:%M"),
        })
        sisa -= isi

    return jadwal


def main():
    # argparse = cara baca argumen yang dikirim saat menjalankan script
    parser = argparse.ArgumentParser(description="Hitung jadwal batch email")
    parser.add_argument("--total", type=int, required=True, help="jumlah email")
    parser.add_argument("--batch", type=int, required=True, help="email per batch")
    parser.add_argument("--delay", type=int, required=True, help="jeda antar batch (menit)")
    parser.add_argument("--mulai", type=str, default=None, help='format "YYYY-MM-DD HH:MM"')
    args = parser.parse_args()

    if args.total < 1 or args.batch < 1 or args.delay < 1:
        raise SystemExit("total, batch, dan delay harus >= 1")

    mulai = datetime.strptime(args.mulai, "%Y-%m-%d %H:%M") if args.mulai else datetime.now()

    jadwal = hitung_jadwal(args.total, args.batch, args.delay, mulai)

    hasil = {
        "total_batch": len(jadwal),
        "selesai_pada": jadwal[-1]["waktu_kirim"],
        "jadwal": jadwal,
    }
    # ensure_ascii=False supaya karakter non-ASCII tidak jadi \uXXXX
    print(json.dumps(hasil, ensure_ascii=False))


if __name__ == "__main__":
    main()
