@extends('layouts.app')

@section('content')

<style id="umPageTheme">
    /* TEMA USER MASTER (tampilan saja, tidak mengubah fungsi) */
    .um-page {
        --x-border: #e5eaf1;
        --x-muted: #64748b;
        --x-ink: #0f172a;
        --x-accent: #2563eb;
    }

    .um-page .pg-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        padding-bottom: 18px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--x-border);
    }

    .um-page .pg-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--x-accent);
        margin-bottom: 6px;
    }

    .um-page .pg-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--x-ink);
        margin: 0 0 4px;
    }

    .um-page .pg-subtitle {
        font-size: 13.5px;
        color: var(--x-muted);
        margin: 0;
    }

    .um-page .pg-panel {
        background: #fff;
        border: 1px solid var(--x-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .um-page .pg-panel-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--x-border);
    }

    .um-page .pg-panel-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--x-ink);
        margin: 0;
    }

    .um-page .pg-panel-body {
        padding: 20px 22px;
    }

    .um-page label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .um-page .form-control,
    .um-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 14px;
        color: var(--x-ink);
        box-shadow: none;
    }

    .um-page .form-control:focus,
    .um-page .form-select:focus {
        border-color: var(--x-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .um-page .form-control::placeholder {
        color: #94a3b8;
    }

    .um-page .btn:not(.note-btn) {
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 18px;
        transition: all .2s ease;
    }

    .um-page .btn-sm:not(.note-btn) {
        padding: 5px 14px;
        font-size: 13px;
    }

    .um-page .btn-primary {
        background: var(--x-accent);
        border-color: var(--x-accent);
    }

    .um-page .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .um-page .btn-secondary {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .um-page .btn-secondary:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--x-ink);
    }

    .um-page .btn-warning:not(.note-btn) {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .um-page .btn-warning:not(.note-btn):hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--x-ink);
    }

    .um-page .btn-danger {
        background: #fff;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .um-page .btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    .um-page .pg-table-wrap {
        overflow: auto;
        border: 1px solid var(--x-border);
        border-radius: 10px;
    }

    .um-page .pg-table {
        margin: 0;
        font-size: 13px;
    }

    .um-page .pg-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--x-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .um-page .pg-table td {
        padding: 12px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .um-page .pg-table tbody tr:hover {
        background: #fafbfd;
    }

    .um-page .pg-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        background: #dbeafe;
        color: #1e40af;
    }

    .um-page .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }

    .um-page .modal-header {
        background: #0f172a;
        color: #fff;
        border-bottom: 0;
        padding: 16px 22px;
    }

    .um-page .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
    }

    .um-page .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .um-page .modal-footer {
        border-top: 1px solid var(--x-border);
        background: #f8fafc;
    }
</style>

<div class="container-fluid um-page">

    <!-- TITLE -->
    <div class="pg-header">

        <div>

            <div class="pg-eyebrow">Master Data</div>

            <h1 class="pg-title">User Master</h1>

            <p class="pg-subtitle">
                Kelola user login system
            </p>

        </div>

        <!-- ADD BUTTON -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">

            Add User

        </button>

    </div>


    <!-- MODAL ADD -->
    <div class="modal fade" id="addUserModal" tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <form action="/user-master/store" method="POST">

                    @csrf

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Tambah User
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <div class="row">

                            <!-- NAME -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Nama User
                                </label>

                                <input type="text" name="name" class="form-control" placeholder="Nama User" required>

                            </div>

                            <!-- EMAIL -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email" name="email" class="form-control" placeholder="Email Login"
                                    required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    SMTP Password
                                </label>

                                <input
                                    type="password"
                                    name="smtp_password"
                                    class="form-control"
                                    placeholder="Password Email">

                            </div>

                            <div class="mb-3">
                                <label>CC Email</label>

                                <textarea
                                    name="cc"
                                    class="form-control"
                                    rows="3"></textarea>

                                <small>
                                    Pisahkan dengan titik koma (;)
                                </small>
                            </div>

                            <!-- ROLE -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Role
                                </label>

                                <select name="role" class="form-select" required>

                                    <option value="">
                                        Pilih Role
                                    </option>

                                    <option value="admin">
                                        Admin
                                    </option>

                                    <option value="user">
                                        User
                                    </option>
                                    <option value="apo">
                                        Apo1
                                    </option>
                                    <option value="apo2">
                                        Apo2
                                    </option>
                                    <option value="finance">
                                        Finance
                                    </option>

                                </select>

                            </div>

                            <!-- PASSWORD -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <input type="password" name="password" class="form-control" placeholder="Password"
                                    required>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Close

                        </button>

                        <button class="btn btn-primary">

                            Save User

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <!-- TABLE -->
    <div class="pg-panel">

        <div class="pg-panel-head">
            <h2 class="pg-panel-title">List User</h2>
        </div>

        <div class="pg-panel-body">

            <div class="pg-table-wrap">

                <table class="table table-hover align-middle pg-table">

                    <thead>

                        <tr>

                            <th width="70">
                                ID
                            </th>

                            <th>
                                Nama
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                CC
                            </th>


                            <th width="120">
                                Role
                            </th>

                            <th width="180">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($users as $item)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $item->name }}
                            </td>

                            <td>
                                {{ $item->email }}
                            </td>

                            <td style="white-space: pre-line;">
                                {!! str_replace(';', "\n", e($item->cc)) !!}
                            </td>

                            <td>

                                <span class="pg-tag">

                                    {{ ucfirst($item->role) }}

                                </span>

                            </td>

                            <td>

                              <div class="d-flex gap-2">

                                {{-- MASTER & ADMIN bisa edit semua --}}
                                @if(in_array(auth()->user()->role, ['master','admin']))

                                <button
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editUser{{ $item->id }}">

                                    Edit

                                </button>

                                {{-- USER hanya bisa edit dirinya sendiri --}}
                                @elseif(
                                (auth()->user()->role == 'user' ||
                                auth()->user()->role == 'apo' ||
                                auth()->user()->role == 'apo2' ||
                                auth()->user()->role == 'finance') &&
                                auth()->id() == $item->id
                                )


                                <button
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editUser{{ $item->id }}">

                                    Password

                                </button>

                                @endif


                                {{-- Hapus hanya Master & Admin --}}
                                @if(in_array(auth()->user()->role, ['master','admin']))

                                <form
                                    action="/user-master/delete/{{ $item->id }}"
                                    method="POST"
                                    style="display:inline"
                                    onsubmit="return confirm('Yakin hapus user?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>

                                </form>

                                @endif

                              </div>

                            </td>



                            <!-- MODAL EDIT -->
                            <div class="modal fade" id="editUser{{ $item->id }}" tabindex="-1">

                                <div class="modal-dialog">

                                    <div class="modal-content">

                                        <form action="/user-master/update/{{ $item->id }}" method="POST">

                                            @csrf

                                            <div class="modal-header">

                                                <h5 class="modal-title">

                                                    Edit User

                                                </h5>

                                                <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                </button>

                                            </div>

                                            <div class="modal-body">

                                                @if(auth()->user()->role == 'user')

                                                <div class="alert alert-info">
                                                    Anda hanya dapat mengubah password akun sendiri.
                                                </div>

                                                <div class="mb-3">
                                                    <label>Password Baru</label>
                                                    <input
                                                        type="password"
                                                        name="password"
                                                        class="form-control"
                                                        required>
                                                </div>

                                                @else

                                                <div class="mb-3">

                                                    <label>Nama</label>

                                                    <input
                                                        type="text"
                                                        name="name"
                                                        class="form-control"
                                                        value="{{ $item->name }}"
                                                        required>

                                                </div>

                                                <div class="mb-3">

                                                    <label>Email</label>

                                                    <input
                                                        type="email"
                                                        name="email"
                                                        class="form-control"
                                                        value="{{ $item->email }}"
                                                        required>

                                                </div>

                                                <div class="mb-3">

                                                    <label>SMTP Password</label>

                                                    <input
                                                        type="password"
                                                        name="smtp_password"
                                                        class="form-control"
                                                        value="{{ $item->smtp_password }}">

                                                </div>

                                                <div class="mb-3">
                                                    <label>CC Email</label>

                                                    <textarea
                                                        name="cc"
                                                        class="form-control"
                                                        rows="3">{{ $item->cc }}</textarea>
                                                </div>


                                                <div class="mb-3">

                                                    <label>Role</label>

                                                    <select name="role" class="form-select">

                                                        <option value="admin"
                                                            {{ $item->role == 'admin' ? 'selected' : '' }}>
                                                            Admin
                                                        </option>

                                                        <option value="user"
                                                            {{ $item->role == 'user' ? 'selected' : '' }}>
                                                            User
                                                        </option>
                                                        <option value="apo"
                                                            {{ $item->role == 'apo' ? 'selected' : '' }}>
                                                            Apo1
                                                        </option>
                                                        <option value="apo2"
                                                            {{ $item->role == 'apo2' ? 'selected' : '' }}>
                                                            Apo2
                                                        </option>
                                                        <option value="finance"
                                                            {{ $item->role == 'finance' ? 'selected' : '' }}>
                                                            Finance
                                                        </option>

                                                    </select>

                                                </div>

                                                <div class="mb-3">

                                                    <label>Password Baru</label>

                                                    <input
                                                        type="password"
                                                        name="password"
                                                        class="form-control"
                                                        placeholder="Kosongkan jika tidak diubah">

                                                </div>

                                                @endif

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

                            <td colspan="5" class="text-center text-muted py-4">

                                Belum ada user

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection