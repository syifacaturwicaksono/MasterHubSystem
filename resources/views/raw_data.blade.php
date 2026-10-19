@extends('layouts.app')

@section('content')

<style>
    .modern-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .modern-header {
        background: #f8fafc;
        padding: 20px 24px;
        font-weight: 600;
        font-size: 18px;
        border-bottom: 1px solid #eee;
    }

    .table-modern {
        margin: 0;
        font-size: 14px; /* 🔥 sedikit lebih besar */
    }

    .table-modern thead {
        background: #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .table-modern th {
        border: none !important;
        font-weight: 600;
        color: #555;
        padding: 14px 18px; /* 🔥 LEBIH LUAS */
        text-align: center;
    }

    .table-modern td {
        border: none !important;
        padding: 14px 18px; /* 🔥 LEBIH LUAS */
        vertical-align: middle;
    }

    .table-modern tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: 0.2s;
    }

    .table-modern tbody tr:hover {
        background: #f9fafb;
    }

    .badge-tat {
        background: #fff3cd;
        color: #856404;
        padding: 6px 12px; /* 🔥 lebih besar */
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
    }

    .text-muted-small {
        font-size: 13px;
        color: #777;
    }

    .table-container {
        overflow-x: auto;
    }

    /* 🔥 tambahan: biar kolom nggak kepet */
    .table-modern td,
    .table-modern th {
        white-space: nowrap;
    }
</style>

<div class="container-fluid">

    <div class="modern-card">

        <div class="modern-header">
            📋 Data ISD
        </div>
        <form method="GET" action="/filter-prefix" class="mb-3 d-flex gap-2">

    <input type="text" name="prefix" class="form-control"
        placeholder="Masukkan 3 digit (contoh: 921)"
        value="{{ $prefix ?? '' }}">

    <button type="submit" class="btn btn-primary">
        Filter
    </button>
     {{-- 🔥 RESET --}}
    <a href="/raw-data" class="btn btn-secondary">
        Reset
    </a>

    @if(isset($total))
        <a href="/export-prefix?prefix={{ $prefix }}" class="btn btn-success">
            Export ({{ $total }} data)
        </a>
    @endif
    <button type="submit" class="btn btn-primary">
        kirim email
    </button>

</form>
@if(isset($total))
    <div class="mb-2">
        <strong>Total Data: {{ $total }}</strong>
    </div>
@endif

        <div class="table-container">
            <table class="table table-modern">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Case ID</th>
                        <th>Received date</th>
                        <th>Start Repair date</th>
                        <th>Company Name</th>
                        <th>Aging</th>
                        <th>Case Status</th>
                        <th>Company City</th>
                        <th>HP Part no.</th>
                        <th>Part Request date</th>
                        <th>SO no.</th>
                        <th>ETA date</th>
                        <th>Part In date</th>
                        <th>Product no.</th>
                        <th>Product Name</th>
                        <th>Total Case >5d</th>

                    </tr>
                </thead>

                <tbody>
                    @foreach($data as $row)
                    <tr>
                        <td class="text-center">
                            {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}
                        </td>

                        <td><strong>{{ $row->case_id }}</strong></td>

                        <td class="text-muted-small">{{ $row->received_date }}</td>
                        <td class="text-muted-small">{{ $row->start_repair_date }}</td>

                        <td>{{ $row->company_name }}</td>

                        <td class="text-center">
                            <span class="badge-tat">
                                {{ $row->tat_case }}
                            </span>
                        </td>

                        <td>{{ $row->case_status }}</td>
                        <td>{{ $row->company_city }}</td>

                        <td>{{ $row->pn_code ?? '-' }}</td>
                        <td>{{ $row->part_request ?? '-' }}</td>
                        <td>{{ $row->so_no ?? '-' }}</td>
                        <td>{{ $row->eta_date ?? '-' }}</td>
                        <td>{{ $row->partin_date ?? '-' }}</td>

                        <td>{{ $row->product_no }}</td>
                        <td>{{ $row->product_name }}</td>
                        <td class="text-center">1</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

        <div class="p-3 text-center border-top">
            {{ $data->links('pagination::simple-bootstrap-5') }}
        </div>

    </div>

</div>

@endsection