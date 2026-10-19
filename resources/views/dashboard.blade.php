@extends('layouts.app')

@section('content')

<style id="dashPageTheme">
    /* TEMA DASHBOARD (tampilan saja, tidak mengubah fungsi) */
    .dash-page {
        --d-border: #e5eaf1;
        --d-muted: #64748b;
        --d-ink: #0f172a;
        --d-accent: #2563eb;
    }

    .dash-page .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        padding-bottom: 18px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--d-border);
    }

    .dash-page .dash-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--d-accent);
        margin-bottom: 6px;
    }

    .dash-page .dash-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--d-ink);
        margin: 0 0 4px;
    }

    .dash-page .dash-subtitle {
        font-size: 13.5px;
        color: var(--d-muted);
        margin: 0;
    }

    .dash-page .dash-filter {
        min-width: 240px;
    }

    .dash-page .dash-filter label {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
    }

    .dash-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 14px;
        box-shadow: none;
    }

    .dash-page .form-select:focus {
        border-color: var(--d-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .dash-page .dash-section-title {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        color: var(--d-muted);
        margin: 8px 0 14px;
    }

    /* kartu statistik */
    .dash-page .dash-card {
        background: #fff;
        border: 1px solid var(--d-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        height: 100%;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .dash-page .dash-card:hover {
        box-shadow: 0 6px 18px rgba(15, 23, 42, .07);
        transform: translateY(-1px);
    }

    .dash-page .dash-card-body {
        padding: 20px 22px;
    }

    .dash-page .dash-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: var(--d-muted);
        margin: 0 0 8px;
    }

    .dash-page .dash-number {
        font-size: 30px;
        font-weight: 700;
        letter-spacing: -.5px;
        color: var(--d-ink);
        line-height: 1.1;
        margin: 0 0 14px;
    }

    .dash-page .dash-number.is-success {
        color: #15803d;
    }

    .dash-page .dash-rows {
        border-top: 1px solid var(--d-border);
        padding-top: 12px;
    }

    .dash-page .dash-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: var(--d-muted);
        padding: 3px 0;
    }

    .dash-page .dash-row strong {
        color: var(--d-ink);
        font-weight: 600;
    }

    /* kartu per jenis monitoring */
    .dash-page .mon-card .dash-card-body {
        text-align: center;
        padding: 18px 14px;
    }

    .dash-page .mon-name {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        margin: 0 0 8px;
        min-height: 32px;
    }

    .dash-page .mon-total {
        font-size: 13px;
        color: var(--d-muted);
        margin: 0 0 10px;
    }

    .dash-page .mon-chip {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #f1f5f9;
        color: var(--d-muted);
    }

    .dash-page .mon-chip.is-alert {
        background: #fee2e2;
        color: #b91c1c;
    }

    .dash-page .mon-max {
        font-size: 12px;
        color: var(--d-muted);
        margin-top: 8px;
    }

    /* kartu tabel */
    .dash-page .dash-panel {
        background: #fff;
        border: 1px solid var(--d-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        margin-top: 24px;
    }

    .dash-page .dash-panel-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--d-border);
    }

    .dash-page .dash-panel-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--d-ink);
        margin: 0;
    }

    .dash-page .dash-panel-body {
        padding: 20px 22px;
    }

    .dash-page .dash-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--d-border);
        border-radius: 10px;
    }

    .dash-page .dash-table {
        margin: 0;
        font-size: 13px;
    }

    .dash-page .dash-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--d-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .dash-page .dash-table td {
        padding: 11px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .dash-page .dash-table tbody tr:hover {
        background: #fafbfd;
    }

    .dash-page .dash-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    .dash-page .dash-tag-dark {
        background: #0f172a;
        color: #fff;
    }

    .dash-page .dash-tag-blue {
        background: #dbeafe;
        color: #1e40af;
    }

    .dash-page .dash-tag-soft {
        background: #f1f5f9;
        color: #334155;
    }

    .dash-page .dash-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #dcfce7;
        color: #166534;
    }

    .dash-page .dash-empty {
        text-align: center;
        color: var(--d-muted);
        padding: 28px 0 !important;
    }

    .dash-page .pagination {
        margin: 0;
    }
</style>

<div class="dash-page">

    <div class="dash-header">
        <div>
            <div class="dash-eyebrow">Monitoring</div>
            <h1 class="dash-title">Dashboard Email Terkirim</h1>
            <p class="dash-subtitle">Ringkasan pengiriman email dan statistik case per jenis monitoring.</p>
        </div>

        {{-- ★★★ DITAMBAHKAN: filter "lihat sebagai" khusus admin ★★★ --}}
        {{-- [DIPERBAIKI] selected sekarang dicocokkan dengan value option itu sendiri
             (finance / apo). Sebelumnya dibandingkan dengan 'fin1' / 'apo1', sehingga
             setelah memilih FIN1/APO1 dropdown tampak kembali ke "Semua (All)" dan
             memilih "Semua (All)" lagi tidak memicu perubahan (tidak bisa kembali ke semua). --}}
        @if(auth()->user()->role === 'admin')
        <form method="GET" action="{{ url()->current() }}" class="dash-filter">
            <label class="form-label d-block">Lihat sebagai</label>
            <select name="role_filter" class="form-select" onchange="this.form.submit()">
                <option value="all" {{ ($roleFilter ?? 'all') == 'all' ? 'selected' : '' }}>Semua (All)</option>
                <option value="apo2" {{ ($roleFilter ?? '') == 'apo2' ? 'selected' : '' }}>APO2 (WIP - MPR)</option>
                <option value="finance" {{ ($roleFilter ?? '') == 'finance' ? 'selected' : '' }}>FIN1 (Pembayaran & Reminder)</option>
                <option value="apo" {{ ($roleFilter ?? '') == 'apo' ? 'selected' : '' }}>APO1 (PO Labor)</option>
            </select>
        </form>
        @endif
    </div>

    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="dash-card">
                <div class="dash-card-body">
                    <p class="dash-label">Total email terkirim</p>
                    <div class="dash-number is-success">{{ $stats['total'] }}</div>

                    <!-- ★★★ DIUBAH CLAUDE: digabung dengan kartu "Terkirim hari ini" yang
                         sebelumnya terpisah, sekarang jadi breakdown Hari ini / Bulan ini /
                         Bulan lalu dalam satu kartu (pola sama seperti kartu Total case). ★★★ -->
                    <div class="dash-rows">
                        <div class="dash-row"><span>Hari ini</span><strong>{{ $stats['hari_ini'] }}</strong></div>
                        <div class="dash-row"><span>Bulan ini</span><strong>{{ $stats['bulan_ini'] }}</strong></div>
                        <div class="dash-row"><span>Bulan lalu</span><strong>{{ $stats['bulan_lalu'] }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="dash-card">
                <div class="dash-card-body">
                    <p class="dash-label">Company unik</p>
                    <div class="dash-number">{{ $stats['company'] }}</div>

                    <!-- ★★★ DIUBAH CLAUDE (BARU): breakdown bulan ini / bulan lalu ★★★ -->
                    <div class="dash-rows">
                        <div class="dash-row"><span>Bulan ini</span><strong>{{ $stats['company_bulan_ini'] }}</strong></div>
                        <div class="dash-row"><span>Bulan lalu</span><strong>{{ $stats['company_bulan_lalu'] }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="dash-card">
                <div class="dash-card-body">
                    <p class="dash-label">Total case</p>
                    <div class="dash-number">{{ $stats['case'] }}</div>

                    <!-- ★★★ DIUBAH CLAUDE (BARU): breakdown hari ini / bulan ini / bulan lalu ★★★ -->
                    <div class="dash-rows">
                        <div class="dash-row"><span>Hari ini</span><strong>{{ $stats['case_hari_ini'] }}</strong></div>
                        <div class="dash-row"><span>Bulan ini</span><strong>{{ $stats['case_bulan_ini'] }}</strong></div>
                        <div class="dash-row"><span>Bulan lalu</span><strong>{{ $stats['case_bulan_lalu'] }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ★★★ DIUBAH CLAUDE (BARU): statistik ringkas per jenis monitoring (Total Case & Aging) ★★★ -->
    <div class="dash-section-title">Statistik per Jenis Monitoring</div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">

        @foreach($monitoringStats as $m)

        @php
            // Keliling lingkaran (r=38): 2 * pi() * 38
            $circumference = 238.76;
            $arcLength = round($circumference * $m['percent'] / 100, 2);

            // Format persen: buang desimal ".0" yang tidak perlu (58.6% tetap, 34.0% jadi 34%)
            $percentLabel = rtrim(rtrim(number_format($m['percent'], 1), '0'), '.');
        @endphp

        <div class="col">
            <div class="dash-card mon-card">
                <div class="dash-card-body">

                    <p class="mon-name">
                        {{ $m['label'] }}
                    </p>

                    <svg width="90" height="90" viewBox="0 0 90 90" class="mb-2">
                        <circle cx="45" cy="45" r="38" stroke="#e9eef5" stroke-width="9" fill="none" />
                        <circle
                            cx="45" cy="45" r="38"
                            stroke="#2563eb"
                            stroke-width="9"
                            fill="none"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $arcLength }} {{ $circumference }}"
                            transform="rotate(-90 45 45)"
                        />
                        <text x="45" y="49" text-anchor="middle" font-size="15" font-weight="bold" fill="#0f172a">
                            {{ $percentLabel }}%
                        </text>
                    </svg>

                    <p class="mon-total">
                        {{ $m['total'] }} case
                    </p>

                    <span class="mon-chip {{ $m['aging_alert'] > 0 ? 'is-alert' : '' }}">
                        {{ $m['aging_alert'] }} aging tinggi
                    </span>

                    <div class="mon-max">
                        Max aging: {{ $m['max_aging'] ?? '-' }}
                    </div>

                </div>
            </div>
        </div>

        @endforeach

    </div>

    <div class="dash-panel">

        <div class="dash-panel-head">
            <h2 class="dash-panel-title">Riwayat Email Terkirim</h2>
        </div>

        <div class="dash-panel-body">

            <div class="dash-table-wrap">

                <table class="table table-hover align-middle dash-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Template</th>
                            <th>Kode</th>
                            <th>Company</th>
                            <th>Email Tujuan</th>
                            <th>Total Case</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($dashboard as $row)

                        <tr>
                            <td align="center">
                                {{ $dashboard->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <span class="dash-tag dash-tag-soft">{{ $row->template_name }}</span>
                            </td>

                            <td>
                                <span class="dash-tag dash-tag-dark">
                                    {{ $row->kode_company }}
                                </span>
                            </td>

                            <td>{{ $row->company_name }}</td>

                            <td>{{ $row->recipient }}</td>

                            <td>{{ $row->total_case }}</td>

                            <td style="white-space:nowrap;">{{ $row->sent_at }}</td>

                            <td>
                                <span class="dash-status">
                                    Terkirim
                                </span>
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="8" class="dash-empty">
                                Belum ada email terkirim
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $dashboard->links() }}
            </div>
        </div>

    </div>

    <!-- ★★★ DIUBAH CLAUDE (BARU): History Upload Batch, ditaruh sebelum baris pagination
         "Riwayat Email Terkirim" — struktur & nama field disamakan persis dengan
         import.blade.php (Jenis: badge dark, Batch: badge primary, File, Tanggal Upload). ★★★ -->
    <div class="dash-panel">

        <div class="dash-panel-head">
            <h2 class="dash-panel-title">History Upload Batch</h2>
        </div>

        <div class="dash-panel-body">

            <div class="dash-table-wrap">

                <table class="table align-middle dash-table">

                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Batch</th>
                            <th>File</th>
                            <th>Tanggal Upload</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($uploadHistory as $item)

                        <tr>

                            <td>
                                <span class="dash-tag dash-tag-dark">
                                    {{ $item->jenis_upload }}
                                </span>
                            </td>

                            <td>
                                <span class="dash-tag dash-tag-blue">
                                    {{ $item->kode_upload }}
                                </span>
                            </td>

                            <td>
                                {{ $item->file_name }}
                            </td>

                            <td style="white-space:nowrap;">
                                {{ $item->created_at }}
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="4" class="dash-empty">
                                Belum ada upload
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $uploadHistory->links() }}
            </div>

        </div>

    </div>

</div>

@endsection