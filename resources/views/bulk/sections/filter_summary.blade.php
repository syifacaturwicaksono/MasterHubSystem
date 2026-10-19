{{-- [PARTIAL] bulk.sections.filter_summary — bagian dari bulk.blade.php (baris asli 266-409) — Judul, kartu filter (jenis/batch/aging) dan kartu total terpilih --}}
    <!-- TITLE -->
    <div class="bc-header">
        <div class="bc-eyebrow">Email Broadcast</div>
        <h1 class="bc-title">BC Email</h1>
        <p class="bc-subtitle">Pilih company, atur template dan footer, lalu kirim email secara massal.</p>
    </div>

    <!-- FILTER -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="bc-card-title">Filter Monitoring</h5>

            <form method="GET" action="/bulk">
                <div class="row g-3 align-items-end">

                    <!-- Jenis Monitoring -->
                    <div class="col-md-3">
                        <label class="form-label">Jenis Monitoring</label>
                        <select name="jenis_upload" id="jenis_upload" class="form-select">
                            <option value="">Pilih Jenis</option>
                            @if(in_array(auth()->user()->role, ['admin', 'apo2']))
                            <option value="W001-WIP" {{ request('jenis_upload') == 'W001-WIP' ? 'selected' : '' }}>
                                W001-WIP
                            </option>
                            <option value="W002-TAT14D (Pending case 14 days)" {{ request('jenis_upload') == 'W002-TAT14D (Pending case 14 days)' ? 'selected' : '' }}>
                                W002-TAT14D (Pending case 14 days)
                            </option>

                            <option value="W003-TAT5D (Pending case 5 days)" {{ request('jenis_upload') == 'W003-TAT5D (Pending case 5 days)' ? 'selected' : '' }}>
                                W003-TAT5D (Pending case 5 days)
                            </option>

                            <option value="W004-KCI" {{ request('jenis_upload') == 'W004-KCI' ? 'selected' : '' }}>
                                W004-KCI
                            </option>

                            <option value="W005-FR (Finish Repair)" {{ request('jenis_upload') == 'W005-FR (Finish Repair)' ? 'selected' : '' }}>
                                W005-FR (Finish Repair)
                            </option>

                            <option value="M001-MPR" {{ request('jenis_upload') == 'M001-MPR' ? 'selected' : '' }}>
                                M001-MPR
                            </option>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'finance']))
                            <option value="M002-Reminder Invoice" {{ request('jenis_upload') == 'M002-Reminder Invoice' ? 'selected' : '' }}>
                                M002-Reminder Invoice
                            </option>

                            <option value="M003-Pembayaran" {{ request('jenis_upload') == 'M003-Pembayaran' ? 'selected' : '' }}>
                                M003-Pembayaran
                            </option>
                            @endif

                            {{-- ★★★ BARU: KLA — SENGAJA di-disable dulu (belum siap dipakai user),
                                tapi backend/logikanya sudah lengkap & berfungsi. Untuk
                                mengaktifkan nanti, CUKUP hapus atribut "disabled" di bawah ini,
                                tidak perlu ubah apapun yang lain. ★★★ --}}
                            @if(in_array(auth()->user()->role, ['admin', 'kla']))
                            <option value="D001-KLA" {{ request('jenis_upload') == 'D001-KLA' ? 'selected' : '' }}>
                                D001-KLA
                            </option>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'apo']))
                            <option value="M004-PO Labor" {{ request('jenis_upload') == 'M004-PO Labor' ? 'selected' : '' }}>
                                M004-PO Labor
                            </option>
                            @endif
                        </select>
                    </div>

                    <!-- Batch -->
                    <div class="col-md-3">
                        <label class="form-label">Batch</label>
                        <select name="kode_upload" id="kode_upload" class="form-select">

                            <option value="">Semua Batch</option>

                            @foreach($batches as $batch)
                            <option value="{{ $batch->kode_upload }}">
                                {{ $batch->kode_upload }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Aging -->
                    <div class="col-md-3">
                        <label class="form-label">Aging</label>

                        <input type="text" name="aging_filter" class="form-control" placeholder="Contoh: >5 atau <14"
                            value="{{ request('aging_filter') }}">
                    </div>

                    <!-- Tombol -->
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                Filter
                            </button>

                            <a href="/bulk" class="btn btn-outline-secondary flex-fill">
                                Reset
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- TOTAL SELECTED -->
    <div class="card mb-4">

        <div class="card-body d-flex justify-content-between align-items-center py-3">

            <div>

                <h6 class="mb-1 fw-semibold">
                    Total Dipilih
                </h6>

                <small class="bc-card-note">
                    Jumlah company yang dicentang
                </small>

            </div>

            <div>

                <span id="selectedCount" class="bc-count">

                    0

                </span>

            </div>

        </div>

    </div>
