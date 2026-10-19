{{--
    [DITAMBAHKAN] Halaman RR (Re Repair) - PO Labor

    Muncul setelah user klik "Next" di popup Review Part & Kategori.
    Menampilkan pasangan Case ID yang punya Serial No sama dalam 1 file
    (rule sementara RR, lihat komentar di ImportController::rrPreview()).

    Data belum masuk ke tabel apo di titik ini — baru masuk kalau salah
    satu tombol di bawah (Export / Simpan / Simpan & Export) diklik.
--}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            🔁 Review RR (Re Repair) — {{ $kodeUpload }}
        </h4>
    </div>

    <div class="alert alert-info">
        Rule RR sementara: dalam 1 file yang sama, Case ID dengan
        <strong>Serial No yang sama</strong> dianggap RR. Case dari file
        bulan sebelumnya belum ikut dibandingkan.
    </div>

    @if (empty($rrGroups))

        <div class="alert alert-secondary">
            Tidak ada RR terdeteksi di file ini.
        </div>

    @else

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Serial No</th>
                        <th>Case ID</th>
                        <th>Company</th>
                        <th>Product Tower</th>
                        <th>Product Name</th>
                        <th>Case Status</th>
                        <th>Repair Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rrGroups as $serialNo => $cases)
                        @foreach ($cases as $i => $case)
                            <tr class="{{ $i === 0 ? 'table-warning' : '' }}">
                                @if ($i === 0)
                                    <td rowspan="{{ count($cases) }}" class="fw-semibold align-middle">
                                        {{ $serialNo }}
                                    </td>
                                @endif
                                <td>{{ $case['case_id'] }}</td>
                                <td>{{ $case['company_name'] }}</td>
                                <td>{{ $case['product_tower'] }}</td>
                                <td>{{ $case['product_name'] }}</td>
                                <td>{{ $case['case_status'] }}</td>
                                <td>{{ $case['repair_action'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif

    {{-- [DITAMBAHKAN] Tiga tombol yang sebelumnya ada di popup, sekarang
         di sini. Masing-masing POST ke upload.rrConfirm dengan action
         berbeda, supaya controller tahu commit + (nanti) export atau
         cuma salah satu. --}}
    <form action="{{ route('upload.rrConfirm') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="kode_upload" value="{{ $kodeUpload }}">
        <input type="hidden" name="action" value="export">
        <button type="submit" class="btn btn-outline-secondary">
            📤 Export
        </button>
    </form>

    <form action="{{ route('upload.rrConfirm') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="kode_upload" value="{{ $kodeUpload }}">
        <input type="hidden" name="action" value="simpan">
        <button type="submit" class="btn btn-primary">
            💾 Simpan
        </button>
    </form>

    <form action="{{ route('upload.rrConfirm') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="kode_upload" value="{{ $kodeUpload }}">
        <input type="hidden" name="action" value="simpan_export">
        <button type="submit" class="btn btn-success">
            💾📤 Simpan &amp; Export
        </button>
    </form>

</div>
@endsection
