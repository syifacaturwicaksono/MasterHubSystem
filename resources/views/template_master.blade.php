@extends('layouts.app')

@section('content')

@include('template_master.styles.page')

<div class="container-fluid tm-page">

    @include('template_master.sections.header_add_modal')



    <!-- LIST -->
    <div class="tm-panel">

        <div class="tm-panel-head">
            <h2 class="tm-panel-title">List Template</h2>
        </div>

        <div class="tm-panel-body">
            <div class="row mb-3">

                <div class="col-md-4">

                    <select
                        id="filterMonitoring"
                        class="form-select">

                        <option value="">
                            Semua Monitoring
                        </option>

                        @if(in_array(auth()->user()->role, ['admin', 'apo2']))
                        <option value="W001-WIP">W001-WIP</option>
                        <option value="W002-TAT14D (Pending case 14 days)">W002-TAT14D (Pending case 14 days)</option>
                        <option value="W003-TAT5D (Pending case 5 days)">W003-TAT5D (Pending case 5 days)</option>
                        <option value="W004-KCI">W004-KCI</option>
                        <option value="W005-FR (Finish Repair)">W005-FR (Finish Repair)</option>
                        <option value="M001-MPR">M001-MPR</option>
                        <option value="M002-Reminder Invoice">M002-Reminder Invoice</option>
                        <option value="M003-Pembayaran">M003-Pembayaran</option>
                        <option value="D001-KLA">D001-KLA</option>
                        <option value="M004-PO Labor">M004-PO Labor</option>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'finance']))
                        <option value="M002-Reminder Invoice">M002-Reminder Invoice</option>
                        <option value="M003-Pembayaran">M003-Pembayaran</option>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'apo']))
                        <option value="M004-PO Labor">M004-PO Labor</option>
                        @endif
                        @if(in_array(auth()->user()->role, ['admin', 'kla']))
                        <option value="D001-KLA">D001-KLA</option>
                        @endif

                    </select>

                </div>

            </div>

            <div class="tm-table-wrap">

                <table
                    class="table table-hover align-middle tm-table">

                    <thead>

                        <tr>

                            <th width="60">
                                No
                            </th>

                            <th>
                                Jenis Monitoring
                            </th>

                            <th>
                                Nama Template
                            </th>

                            <th>
                                Subject
                            </th>
                            <th>
                                Ketegori
                            </th>

                            <th width="180">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($templates as $item)

                        <tr data-monitoring="{{ $item->jenis_monitoring }}">

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>

                                <strong>
                                    {{ $item->jenis_monitoring }}
                                </strong>

                            </td>

                            <td>

                                <strong>
                                    {{ $item->nama_template }}
                                </strong>

                            </td>

                            <td>

                                {{ $item->subject }}

                            </td>

                            <td>
                                @if(auth()->user()->role == 'admin' || $item->kategori == auth()->user()->role)
                                <span class="tm-tag">{{ $item->kategori }}</span>
                                @endif
                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <!-- EDIT -->
                                    <button
                                        class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $item->id }}">

                                        Edit

                                    </button>

                                    <!-- DELETE -->
                                    @if(auth()->user()->role == 'admin')

                                    <form
                                        action="/template-master/delete/{{ $item->id }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin hapus template ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-danger btn-sm">

                                            Hapus

                                        </button>

                                    </form>

                                    @endif

                                </div>

                            </td>

                        </tr>



                        @include('template_master.modals.edit_template')

                        @empty

                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted py-4">

                                Belum ada template

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


@include('template_master.scripts.summernote')
<!---/Yogi-->
@endsection