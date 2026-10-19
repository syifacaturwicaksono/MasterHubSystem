<div align="center">

# 📬 MasterHubSystem

**Master Hub CRM untuk pengiriman email ke partner dan pengingat otomatis.**

Timer dan penjadwal berbasis Python, berjalan sebagai Windows Service melalui NSSM.

![Python](https://img.shields.io/badge/Python-3.x-3776AB?style=for-the-badge&logo=python&logoColor=white)
![Windows Service](https://img.shields.io/badge/Windows-Service-0078D6?style=for-the-badge&logo=windows&logoColor=white)
![NSSM](https://img.shields.io/badge/NSSM-Service%20Manager-2F3542?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Active-2ea44f?style=for-the-badge)

[Fitur](#-fitur) · [Arsitektur](#-arsitektur) · [Instalasi](#-instalasi) · [Menjalankan sebagai Service](#-menjalankan-sebagai-windows-service-nssm) · [Struktur Folder](#-struktur-folder)

</div>

---

## ✨ Tentang Project

**MasterHubSystem** adalah pusat kendali (hub) dalam sistem CRM yang mengatur komunikasi dengan partner. Sistem ini mengirim email secara otomatis dan menjalankan pengingat (reminder) sesuai jadwal, tanpa perlu dijalankan manual setiap hari.

Master Hub merupakan bagian dari sistem CRM yang lebih besar bersama modul **CRM CM** dan **CRM CS**.

## 🚀 Fitur

- 📧 **Pengiriman email ke partner** secara otomatis
- ⏰ **Reminder terjadwal** dengan timer berbasis Python
- 🖥️ **Berjalan sebagai Windows Service** via NSSM: otomatis aktif saat komputer menyala dan restart sendiri jika terhenti
- 🏷️ **Klasifikasi kategori part** menggunakan Naive Bayes *(bagian dari penelitian skripsi)*
- 🌐 **Antarmuka web** untuk pengelolaan data dan pengaturan

## 🧩 Arsitektur

```text
┌──────────────┐     ┌──────────────┐
│   CRM CM     │     │   CRM CS     │
│ (PHP+Baileys)│     │ (PHP+Baileys)│
└──────┬───────┘     └──────┬───────┘
       │                    │
       └─────────┬──────────┘
                 ▼
        ┌─────────────────┐       ┌──────────────┐
        │  Master Hub     │──────▶│ Email Partner│
        │  (Web + Python) │       └──────────────┘
        │  Timer/Scheduler│       ┌──────────────┐
        └────────┬────────┘──────▶│   Reminder   │
                 │                └──────────────┘
                 ▼
        ┌─────────────────┐
        │    Database     │
        └─────────────────┘
```

> Sesuaikan diagram di atas dengan alur sebenarnya dari sistem Anda.

## 🛠️ Tech Stack

| Bagian | Teknologi |
| --- | --- |
| Timer / Penjadwal | Python |
| Service Manager | NSSM |
| Web / Backend | `[ISI: mis. PHP / CodeIgniter]` |
| Database | `[ISI: mis. MySQL]` |
| Frontend | `[ISI: mis. HTML, CSS, JavaScript]` |
| Klasifikasi | Naive Bayes |

## 📸 Tampilan

<!-- Ganti dengan screenshot asli. Simpan gambar di folder docs/ -->
| Dashboard | Pengaturan Reminder |
| --- | --- |
| ![Dashboard](docs/dashboard.png) | ![Reminder](docs/reminder.png) |

## ⚙️ Instalasi

### Prasyarat

- Windows 10/11 atau Windows Server
- Python 3.x
- [NSSM](https://nssm.cc/download)
- Web server dan database sesuai kebutuhan project

### Langkah

```bash
# 1. Clone repositori
git clone https://github.com/syifacaturwicaksono/MasterHubSystem.git
cd MasterHubSystem

# 2. (Opsional) Buat virtual environment
python -m venv venv
venv\Scripts\activate

# 3. Pasang dependensi
pip install -r requirements.txt
```

### Konfigurasi

Salin file contoh konfigurasi lalu isi sesuai lingkungan Anda:

```bash
copy .env.example .env
```

```env
# Contoh isi .env (jangan pernah commit file .env asli!)
DB_HOST=localhost
DB_NAME=masterhub
DB_USER=your_user
DB_PASS=your_password

SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=your_email@example.com
SMTP_PASS=your_email_password
```

> ⚠️ **Penting:** jangan pernah mengunggah file `.env`, password, atau token ke repositori publik. Pastikan `.env` sudah masuk `.gitignore`.

## 🪟 Menjalankan sebagai Windows Service (NSSM)

Agar timer berjalan terus di latar belakang, daftarkan sebagai service:

```bat
:: Daftarkan service (sesuaikan path Python dan skrip Anda)
nssm install MasterHubTimer "C:\Python\python.exe" "C:\MasterHubSystem\timer.py"

:: Atur folder kerja
nssm set MasterHubTimer AppDirectory "C:\MasterHubSystem"

:: Jalankan otomatis saat Windows menyala
nssm set MasterHubTimer Start SERVICE_AUTO_START

:: Mulai service
nssm start MasterHubTimer
```

Perintah berguna lainnya:

| Perintah | Fungsi |
| --- | --- |
| `nssm status MasterHubTimer` | Cek status service |
| `nssm restart MasterHubTimer` | Restart service |
| `nssm stop MasterHubTimer` | Hentikan service |
| `nssm remove MasterHubTimer confirm` | Hapus service |

## 📁 Struktur Folder

```text
MasterHubSystem/
├── docs/              # Screenshot dan dokumentasi
├── app/               # Kode aplikasi web
├── scheduler/         # Skrip timer/penjadwal Python
├── .env.example       # Contoh konfigurasi
├── requirements.txt   # Dependensi Python
└── README.md
```

> Sesuaikan struktur di atas dengan folder project Anda yang sebenarnya.

## 🗺️ Rencana Pengembangan

- [ ] Template email yang dapat disesuaikan per partner
- [ ] Log riwayat pengiriman email dan reminder
- [ ] Notifikasi jika pengiriman gagal

## 🔗 Project Terkait

- **CRM CM**: modul CRM berbasis PHP dengan integrasi WhatsApp (Baileys)
- **CRM CS**: modul CRM berbasis PHP dengan integrasi WhatsApp (Baileys)

## 👤 Penulis

**Syifa Catur Wicaksono**

- GitHub: [@syifacaturwicaksono](https://github.com/syifacaturwicaksono)

---

<div align="center">

Dibuat dengan ☕ dan Python

</div>
