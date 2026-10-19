{{-- [PARTIAL] sparepart_master.sections.saved_list — bagian dari sparepart_master.blade.php (baris asli 225-421) — Daftar data tersimpan (tabel edit inline + paginasi) --}}
<!-- ==========================================================
     LIST DATA TERSIMPAN
========================================================== -->

<div class="card-modern">


    <h5 class="mb-3">
        Daftar Sparepart Tersimpan ({{ $spareparts->total() }})
    </h5>

    <div class="table-responsive">

        <table class="table table-bordered nowrap-table"
            id="smSavedTable">

            <thead>
                <tr>
                    <th style="width:4%">No.</th>
                    <th>Part Number</th>
                    <th style="width:22%">Deskripsi</th>
                    <th style="width:9%">Divisi</th>
                    <th style="width:14%">Kategori</th>
                    <th style="width:12%">Harga</th>
                    <th>Ditambahkan</th>
                    <th style="width:140px">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($spareparts as $i => $sp)

                <tr data-id="{{ $sp->id }}">

                    <td>
                        {{ $spareparts->firstItem() + $i }}
                    </td>

                    <td>
                        {{ $sp->part_number }}
                    </td>

                    <!-- DESKRIPSI -->
                    <td>
                        <span class="sm-view-desk">
                            {{ $sp->deskripsi }}
                        </span>

                        <input type="text"
                            class="form-control form-control-sm sm-edit-desk d-none"
                            value="{{ $sp->deskripsi }}">
                    </td>

                    <!-- DIVISI -->
                    <td>

                        <span class="sm-view-divisi">

                            @if($sp->divisi)

                            <span class="sm-badge-divisi {{ strtolower($sp->divisi) }}">
                                {{ $sp->divisi }}
                            </span>

                            @endif

                        </span>

                        <select class="form-select form-select-sm sm-edit-divisi d-none">

                            <option value="">
                                -- pilih --
                            </option>

                            @foreach($divisiOptions as $div)

                            <option value="{{ $div }}"
                                {{ $div === $sp->divisi ? 'selected' : '' }}>
                                {{ $div }}
                            </option>

                            @endforeach

                        </select>

                    </td>


                    <!-- KATEGORI -->
                    <td>

                        <span class="sm-view-kat">
                            {{ $sp->kategori }}
                        </span>

                        <select class="form-select form-select-sm sm-edit-kat d-none">

                            <option value="">
                                -- pilih kategori --
                            </option>

                            @foreach($categories as $cat)

                            <option value="{{ $cat->name }}"
                                {{ $cat->name === $sp->kategori ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>

                            @endforeach

                        </select>

                    </td>


                    <!-- HARGA -->
                    <td>

                        <span class="sm-view-harga">
                            {{ $sp->harga !== null
                            ? 'Rp '.number_format($sp->harga, 0, ',', '.')
                            : ''
                        }}
                        </span>

                        <select class="form-select form-select-sm sm-edit-harga d-none">

                            <option value="">
                                -- pilih harga --
                            </option>

                            @foreach($hargaOptions as $h)

                            <option value="{{ $h }}"
                                {{ (float) $h === (float) $sp->harga ? 'selected' : '' }}>

                                Rp {{ number_format($h, 0, ',', '.') }}

                            </option>

                            @endforeach

                        </select>

                    </td>


                    <!-- CREATED -->
                    <td>
                        {{ \Carbon\Carbon::parse($sp->created_at)->format('d/m/Y H:i') }}
                    </td>


                    <!-- AKSI -->
                    <td>

                        <button type="button"
                            class="btn btn-sm btn-outline-primary sm-edit-btn">
                            ✏️ Edit
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-success sm-save-btn d-none">
                            💾 Simpan
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-secondary sm-cancel-btn d-none">
                            Batal
                        </button>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="8"
                        class="text-center text-muted">
                        Belum ada data.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{ $spareparts->links() }}


</div>
