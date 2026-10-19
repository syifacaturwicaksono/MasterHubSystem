{{-- [PARTIAL] import.sections.upload_history — bagian dari import.blade.php (baris asli 809-877) — Kartu riwayat upload (tabel batch) --}}
        <!-- HISTORY -->
        <div class="up-card">

            <div class="up-card-head">
                <h2 class="up-card-title">History Upload Batch</h2>
            </div>

            <div class="up-card-body">

                <div class="up-table-wrap">

                    <table class="table align-middle up-table">

                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Batch</th>
                                <th>File</th>
                                <th>Tanggal Upload</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($history as $item)

                            <tr>

                                <td>
                                    <span class="up-tag up-tag-dark">
                                        {{ $item->jenis_upload }}
                                    </span>
                                </td>

                                <td>
                                    <span class="up-tag up-tag-blue">
                                        {{ $item->kode_upload }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->file_name }}
                                </td>

                                <td>
                                    {{ $item->created_at }}
                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="4" class="up-empty">
                                    Belum ada upload
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
