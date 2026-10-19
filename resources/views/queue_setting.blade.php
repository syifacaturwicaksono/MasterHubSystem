@extends('layouts.app')

@section('content')

<style>
    .qs-wrap {
        max-width: 720px;
    }

    .qs-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 6px;
    }

    .qs-header-icon {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.25);
    }

    .qs-header-icon svg {
        width: 24px;
        height: 24px;
        stroke: #fff;
    }

    .qs-title {
        font-weight: 700;
        font-size: 1.5rem;
        margin: 0;
        color: #1a1d23;
    }

    .qs-subtitle {
        color: #6c757d;
        font-size: 0.92rem;
        margin: 2px 0 0;
    }

    .qs-scope-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 18px 0 24px;
    }

    .qs-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        background: #eef4ff;
        color: #0a58ca;
        font-size: 0.82rem;
        font-weight: 600;
        border: 1px solid #d7e6ff;
    }

    .qs-chip svg {
        width: 13px;
        height: 13px;
        stroke: #0a58ca;
    }

    .qs-desc {
        color: #6c757d;
        font-size: 0.92rem;
        line-height: 1.6;
        margin-bottom: 26px;
    }

    .qs-success {
        border: none;
        border-radius: 12px;
        background: #e9f9ee;
        color: #1a7f4e;
        font-weight: 500;
        padding: 14px 18px;
    }

    .qs-card {
        border: 1px solid #edf0f3;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(20, 30, 60, 0.05);
        overflow: hidden;
    }

    .qs-card-body {
        padding: 28px;
    }

    .qs-field {
        margin-bottom: 22px;
    }

    .qs-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #1a1d23;
        margin-bottom: 8px;
    }

    .qs-label svg {
        width: 16px;
        height: 16px;
        stroke: #0d6efd;
        flex-shrink: 0;
    }

    .qs-input-group {
        position: relative;
    }

    .qs-input-group input.form-control {
        border-radius: 10px;
        border: 1.5px solid #e3e7ed;
        padding: 10px 90px 10px 14px;
        font-size: 1rem;
        font-weight: 600;
        color: #1a1d23;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .qs-input-group input.form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    .qs-input-suffix {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        color: #9aa2ad;
        font-weight: 600;
        pointer-events: none;
    }

    .qs-hint {
        font-size: 0.8rem;
        color: #8a94a3;
        margin-top: 6px;
    }

    /* Visualisasi timeline batch — murni ilustratif, tidak memengaruhi submit form */
    .qs-timeline-box {
        background: #f8f9fc;
        border: 1px dashed #dde3ee;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 24px;
    }

    .qs-timeline-label {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #9aa2ad;
        margin-bottom: 12px;
    }

    .qs-timeline-track {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0;
    }

    .qs-batch-pill {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #0d6efd;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 8px;
        white-space: nowrap;
    }

    .qs-batch-pill.is-muted {
        background: #dbe4f3;
        color: #6b7a94;
    }

    .qs-gap {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 8px;
        color: #b6bfcc;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 56px;
    }

    .qs-gap-line {
        width: 100%;
        height: 0;
        border-top: 2px dashed #c9d2e0;
        margin-bottom: 4px;
    }

    .qs-timeline-note {
        margin-top: 10px;
        font-size: 0.8rem;
        color: #6c757d;
    }

    .qs-submit {
        border-radius: 10px;
        padding: 10px 26px;
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.25);
        border: none;
    }

    .qs-note {
        display: flex;
        gap: 12px;
        border: none;
        border-radius: 14px;
        background: #fff7e6;
        padding: 16px 18px;
        margin-top: 22px;
    }

    .qs-note svg {
        width: 20px;
        height: 20px;
        stroke: #b8860b;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .qs-note-text {
        font-size: 0.85rem;
        color: #6b5410;
        line-height: 1.55;
        margin: 0;
    }
</style>

<div class="qs-wrap">

    <div class="qs-header">
        <div class="qs-header-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M12 7v5l3 3"></path>
            </svg>
        </div>
        <div>
            <p class="qs-title">Setting Timer Pengiriman</p>
            <p class="qs-subtitle">Atur jarak kirim antar batch supaya aman dari limit SMTP</p>
        </div>
    </div>

    <div class="qs-scope-row">
        <span class="qs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/></svg>
            Pembayaran
        </span>
        <span class="qs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            Reminder Invoice
        </span>
        <span class="qs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            PO Labor
        </span>
        <span class="qs-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 5-6"/></svg>
            MPR
        </span>
    </div>

    <p class="qs-desc">
        Kalau jumlah company yang dicentang lebih banyak dari "Jumlah email per batch",
        sisanya otomatis dijadwalkan kirim setelah jeda yang ditentukan di bawah —
        supaya tidak kena limit pengiriman SMTP.
    </p>

    @if(session('success'))
        <div class="alert qs-success mb-4">{{ session('success') }}</div>
    @endif

    {{-- [DITAMBAHKAN] tampilkan error validasi --}}
    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card qs-card">
        <div class="qs-card-body">

            <form method="POST" action="{{ route('queue-setting.update') }}" id="qsForm">
                @csrf

                <div class="qs-field">
                    <label class="qs-label" for="batch_size">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Jumlah email per batch
                    </label>
                    <div class="qs-input-group">
                        <input
                            type="number"
                            id="batch_size"
                            name="batch_size"
                            class="form-control"
                            min="1"
                            max="1000"
                            value="{{ old('batch_size', $setting->batch_size ?? 30) }}"
                            required
                        >
                        <span class="qs-input-suffix">email</span>
                    </div>
                    <div class="qs-hint">Contoh: 30 artinya kirim 30 email dulu, sisanya nunggu jeda.</div>
                </div>

                <div class="qs-field">
                    <label class="qs-label" for="delay_minutes">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        Jeda antar batch
                    </label>
                    <div class="qs-input-group">
                        <input
                            type="number"
                            id="delay_minutes"
                            name="delay_minutes"
                            class="form-control"
                            min="1"
                            max="1440"
                            value="{{ old('delay_minutes', $setting->delay_minutes ?? 60) }}"
                            required
                        >
                        <span class="qs-input-suffix">menit</span>
                    </div>
                    <div class="qs-hint">Contoh: 60 artinya batch berikutnya dikirim 1 jam setelah batch sebelumnya.</div>
                </div>

                <!-- Ilustrasi timeline batch, murni visual — tidak mempengaruhi data form -->
                <div class="qs-timeline-box">
                    <div class="qs-timeline-label">Gambaran alur pengiriman</div>
                    <div class="qs-timeline-track" id="qsTimelineTrack">
                        <span class="qs-batch-pill">Batch 1</span>
                        <span class="qs-gap">
                            <span class="qs-gap-line"></span>
                            <span id="qsGapLabel">60 mnt</span>
                        </span>
                        <span class="qs-batch-pill is-muted">Batch 2</span>
                        <span class="qs-gap">
                            <span class="qs-gap-line"></span>
                            <span id="qsGapLabel2">60 mnt</span>
                        </span>
                        <span class="qs-batch-pill is-muted">Batch 3</span>
                    </div>
                    <p class="qs-timeline-note" id="qsTimelineNote">
                        Tiap batch berisi 30 email, jeda 60 menit sebelum lanjut ke batch berikutnya.
                    </p>
                </div>

                {{-- [DITAMBAHKAN] Simulasi jadwal — dihitung script Python, tidak menyimpan/mengirim apa pun --}}
                <div class="qs-timeline-box">
                    <div class="qs-timeline-label">Simulasi jadwal</div>
                    <p class="qs-hint" style="margin-top:-4px; margin-bottom:12px;">
                        Isi perkiraan jumlah email, lalu klik "Hitung jadwal" untuk melihat berapa batch
                        dan jam berapa tiap batch dikirim, berdasarkan angka di atas. Hanya simulasi
                        &mdash; tidak mengirim email dan tidak menyimpan setting.
                    </p>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="number" id="simTotalEmail" class="form-control" style="max-width:160px;"
                               min="1" value="120" placeholder="Total email">
                        <button type="button" id="btnSimulasi" class="btn btn-outline-primary btn-sm">
                            Hitung jadwal
                        </button>
                    </div>
                    <div id="simResult" class="mt-3"></div>
                </div>

                <button type="submit" class="btn btn-primary qs-submit">Simpan</button>
            </form>

        </div>
    </div>

    {{-- [DITAMBAHKAN] Status antrean jadwal saat ini + tombol reset --}}
    <div class="qs-note" style="background:{{ ($setting->next_available_at ?? null) && \Illuminate\Support\Carbon::parse($setting->next_available_at)->isFuture() ? '#eef4ff' : '#e9f9ee' }};">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:{{ ($setting->next_available_at ?? null) && \Illuminate\Support\Carbon::parse($setting->next_available_at)->isFuture() ? '#0a58ca' : '#1a7f4e' }};">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 7v5l3 3"></path>
        </svg>
        <div style="flex:1;">
            <p class="qs-note-text mb-2">
                @if(($setting->next_available_at ?? null) && \Illuminate\Support\Carbon::parse($setting->next_available_at)->isFuture())
                    <strong>Masih ada antrean berjalan.</strong> Slot pengiriman berikutnya
                    (dari fitur manapun — BC Email atau Broadcast All) baru mulai sekitar
                    <strong>{{ \Illuminate\Support\Carbon::parse($setting->next_available_at)->format('d M Y, H:i') }}</strong>.
                @else
                    <strong>Antrean kosong.</strong> Pengiriman berikutnya akan langsung
                    mulai dari sekarang (tidak mengantre di belakang pengiriman lain).
                @endif
            </p>
            <form method="POST" action="{{ route('queue-setting.reset') }}"
                onsubmit="return confirm('Reset antrean? Ini cuma perlu dilakukan kalau jadwal yang tersimpan sudah tidak sesuai kondisi asli (misal ketinggalan dari tes-tes sebelumnya).');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    🔄 Reset Antrean
                </button>
            </form>
        </div>
    </div>

    <div class="qs-note">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
        <p class="qs-note-text">
            <strong>Catatan:</strong> Setting ini juga otomatis dipakai untuk menghindari
            tabrakan jadwal dengan fitur <em>Broadcast All</em> — siapa pun yang klik kirim
            lebih dulu, pengiriman batch berikutnya (dari fitur manapun) otomatis mengantre
            di belakangnya.
        </p>
    </div>

</div>

<script>
    // Murni untuk ilustrasi timeline di atas — tidak menyentuh proses submit/validasi form
    (function () {
        var batchInput = document.getElementById('batch_size');
        var delayInput = document.getElementById('delay_minutes');
        var gap1 = document.getElementById('qsGapLabel');
        var gap2 = document.getElementById('qsGapLabel2');
        var note = document.getElementById('qsTimelineNote');

        function updateTimeline() {
            var batch = parseInt(batchInput.value, 10);
            var delay = parseInt(delayInput.value, 10);

            var batchText = (!isNaN(batch) && batch > 0) ? batch : '30';
            var delayText = (!isNaN(delay) && delay > 0) ? delay + ' mnt' : '60 mnt';

            gap1.textContent = delayText;
            gap2.textContent = delayText;
            note.textContent = 'Tiap batch berisi ' + batchText + ' email, jeda ' + delayText + ' sebelum lanjut ke batch berikutnya.';
        }

        if (batchInput && delayInput) {
            batchInput.addEventListener('input', updateTimeline);
            delayInput.addEventListener('input', updateTimeline);
            updateTimeline();
        }
    })();
</script>

<script>
    // Simulasi jadwal: kirim angka ke Laravel -> Laravel menjalankan script Python -> hasil (JSON) jadi tabel
    (function () {
        var btn    = document.getElementById('btnSimulasi');
        var result = document.getElementById('simResult');
        if (!btn) return;

        btn.addEventListener('click', async function () {
            result.textContent = 'Menghitung...';

            // Pakai isi input yang sedang tampil (belum perlu disimpan dulu)
            var body = new URLSearchParams({
                total_email:   document.getElementById('simTotalEmail').value,
                batch_size:    document.getElementById('batch_size').value,
                delay_minutes: document.getElementById('delay_minutes').value
            });

            try {
                var res = await fetch("{{ route('queue-setting.preview') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: body
                });
                var data = await res.json();

                if (!res.ok) {
                    result.textContent = data.error || data.message || 'Terjadi kesalahan.';
                    return;
                }

                var table = document.createElement('table');
                table.className = 'table table-sm mb-2';
                table.innerHTML = '<thead><tr><th>Batch</th><th>Jumlah email</th><th>Waktu kirim</th></tr></thead>';
                var tbody = document.createElement('tbody');

                data.jadwal.forEach(function (row) {
                    var tr = document.createElement('tr');
                    [row.batch, row.jumlah_email, row.waktu_kirim].forEach(function (val) {
                        var td = document.createElement('td');
                        td.textContent = val;
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);

                var info = document.createElement('div');
                info.className = 'qs-timeline-note';
                info.textContent = data.total_batch + ' batch, selesai sekitar ' + data.selesai_pada;

                result.innerHTML = '';
                result.appendChild(table);
                result.appendChild(info);
            } catch (e) {
                result.textContent = 'Gagal menghubungi server.';
            }
        });
    })();
</script>

@endsection