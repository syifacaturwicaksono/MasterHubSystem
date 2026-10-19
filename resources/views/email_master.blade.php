@extends('layouts.app')

@section('content')

<style id="emPageTheme">
    /* TEMA EMAIL MASTER (tampilan saja, tidak mengubah fungsi) */
    .em-page {
        --e-border: #e5eaf1;
        --e-muted: #64748b;
        --e-ink: #0f172a;
        --e-accent: #2563eb;
    }

    .em-page .em-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        padding-bottom: 18px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--e-border);
    }

    .em-page .em-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--e-accent);
        margin-bottom: 6px;
    }

    .em-page .em-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--e-ink);
        margin: 0 0 4px;
    }

    .em-page .em-subtitle {
        font-size: 13.5px;
        color: var(--e-muted);
        margin: 0;
    }

    .em-page .em-panel {
        background: #fff;
        border: 1px solid var(--e-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .em-page .em-panel-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--e-border);
    }

    .em-page .em-panel-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--e-ink);
        margin: 0;
    }

    .em-page .em-panel-body {
        padding: 20px 22px;
    }

    .em-page label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .em-page .form-control,
    .em-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 14px;
        color: var(--e-ink);
        box-shadow: none;
    }

    .em-page .form-control:focus,
    .em-page .form-select:focus {
        border-color: var(--e-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .em-page .form-control::placeholder {
        color: #94a3b8;
    }

    .em-page .btn {
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 18px;
        transition: all .2s ease;
    }

    .em-page .btn-sm {
        padding: 5px 14px;
        font-size: 13px;
    }

    .em-page .btn-primary {
        background: var(--e-accent);
        border-color: var(--e-accent);
    }

    .em-page .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .em-page .btn-secondary {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .em-page .btn-secondary:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--e-ink);
    }

    .em-page .btn-warning {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .em-page .btn-warning:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--e-ink);
    }

    .em-page .btn-danger {
        background: #fff;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .em-page .btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    .em-page .em-table-wrap {
        overflow: auto;
        border: 1px solid var(--e-border);
        border-radius: 10px;
    }

    .em-page .em-table {
        margin: 0;
        font-size: 13px;
    }

    .em-page .em-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--e-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .em-page .em-table td {
        padding: 12px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .em-page .em-table tbody tr:hover {
        background: #fafbfd;
    }

    .em-page .em-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    .em-page .em-tag-dark {
        background: #0f172a;
        color: #fff;
    }

    .em-page .em-tag-blue {
        background: #dbeafe;
        color: #1e40af;
    }

    .em-page .em-tag-mail {
        background: #e8f5ee;
        color: #166534;
        font-weight: 500;
    }

    .em-page .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }

    .em-page .modal-header {
        background: #0f172a;
        color: #fff;
        border-bottom: 0;
        padding: 16px 22px;
    }

    .em-page .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
    }

    .em-page .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .em-page .modal-footer {
        border-top: 1px solid var(--e-border);
        background: #f8fafc;
    }
</style>

    <div class="container-fluid em-page">

        <!-- TITLE -->
        <div class="em-header">

            <div>
                <div class="em-eyebrow">Master Data</div>

                <h1 class="em-title">Email Master</h1>

                <p class="em-subtitle">
                    Mapping company code ke email
                </p>
            </div>

            <!-- ADD BUTTON -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmailModal">

                Add Email

            </button>

        </div>


        <!-- MODAL ADD -->
        <div class="modal fade" id="addEmailModal" tabindex="-1">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <form action="/email-master/store" method="POST">

                        @csrf

                        <div class="modal-header">

                            <h5 class="modal-title">
                                Tambah Email
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                            </button>

                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="col-md-3 mb-3">

                                    <label class="form-label fw-semibold">
                                        Kode Company
                                    </label>

                                    <input type="text" name="kode_company" class="form-control" placeholder="Contoh: 511"
                                        required>

                                </div>

                                <div class="col-md-4 mb-3">

                                    <label class="form-label fw-semibold">
                                        Company Name
                                    </label>

                                    <input type="text" name="company_name" class="form-control" placeholder="Nama Company"
                                        required>

                                </div>
                                <div class="col-md-4 mb-3">

                                    <label class="form-label fw-semibold">
                                        Kategori
                                    </label>

                                    <input type="text" name="kategori" class="form-control" placeholder="Apo,Finance,KLA"
                                        required>

                                </div>


                                <div class="col-md-5 mb-3">

                                    <label class="form-label fw-semibold">
                                        Email
                                    </label>

                                    <textarea name="email" rows="3" class="form-control"
                                        placeholder="email1@mail.com,email2@mail.com" required></textarea>

                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                                Close

                            </button>

                            <button class="btn btn-primary">

                                Save Email

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        <!-- TABLE -->
        <div class="em-panel">

            <div class="em-panel-head">
                <h2 class="em-panel-title">List Email</h2>
            </div>

            <div class="em-panel-body">

                <div>
                    <div class="mb-3">
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Cari kode company, nama company, atau email...">
                    </div>

                    <div class="em-table-wrap">

                    <table class="table table-hover align-middle em-table" id="emailTable">

                        <thead>

                            <tr>
                                <th>
                                    No
                                </th>

                                <th width="80">
                                    Kode
                                </th>

                                <th>
                                    Company
                                </th>

                                <th>
                                    Email
                                </th>
                                <th>
                                    Kategori
                                </th>

                                <th width="170">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($emails as $item)

                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <span class="em-tag em-tag-dark">
                                            {{ $item->kode_company }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $item->company_name }}
                                    </td>

                                    <td>
                                        {{ $item->email }}
                                    </td>
                                    <td>
                                        <span class="em-tag em-tag-blue">{{ $item->kategori }}</span>
                                    </td>

                                    <td>

                                      <div class="d-flex gap-2">

                                        <!-- EDIT -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $item->id }}">

                                            Edit

                                        </button>

                                        <!-- DELETE -->
                                        @if(auth()->user()->role == 'admin')

                                            <form action="/email-master/delete/{{ $item->id }}" method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin hapus data?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">

                                                    Hapus

                                                </button>

                                            </form>

                                        @endif

                                      </div>

                                    </td>

                                </tr>


                                <!-- MODAL EDIT -->
                                <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">

                                    <div class="modal-dialog">

                                        <div class="modal-content">

                                            <form action="/email-master/update/{{ $item->id }}" method="POST">

                                                @csrf

                                                <div class="modal-header">

                                                    <h5 class="modal-title">
                                                        Edit Email
                                                    </h5>

                                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                    </button>

                                                </div>

                                                <div class="modal-body">

                                                    <div class="mb-3">

                                                        <label>
                                                            Kode Company
                                                        </label>

                                                        <input type="text" name="kode_company" class="form-control"
                                                            value="{{ $item->kode_company }}" required>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label>
                                                            Company Name
                                                        </label>

                                                        <input type="text" name="company_name" class="form-control"
                                                            value="{{ $item->company_name }}" required>

                                                    </div>
                                                     <div class="mb-3">

                                                        <label>
                                                            Kategori
                                                        </label>

                                                        <input type="text" name="kategori" class="form-control"
                                                            value="{{ $item->kategori }}" placeholder="Apo,Finance,KLA" required>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label>
                                                            Email
                                                        </label>

                                                        <textarea name="email" rows="4" class="form-control"
                                                            required>{{ $item->email }}</textarea>

                                                    </div>

                                                </div>

                                                <div class="modal-footer">

                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                                                        Close

                                                    </button>

                                                    <button class="btn btn-primary">

                                                        Update

                                                    </button>

                                                </div>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            @empty

                                <tr>

                                    <td colspan="4" class="text-center text-muted py-4">

                                        Belum ada data email

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {

            let filter = this.value.toLowerCase();

            let rows = document.querySelectorAll('#emailTable tbody tr');

            rows.forEach(function (row) {

                let text = row.innerText.toLowerCase();

                if (text.indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }

            });

        });
    </script>
@endsection