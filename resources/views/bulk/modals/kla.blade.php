{{-- [PARTIAL] bulk.modals.kla — bagian dari bulk.blade.php (baris asli 1162-1220) — Modal preview — jenis D001-KLA --}}
                {{-- ★★★ BARU: Modal KLA — logikanya sudah lengkap & berfungsi, hanya
                    opsi dropdown-nya yang sengaja di-disable dulu (lihat bagian atas). ★★★ --}}
                <div class="modal fade" id="modal{{ $item->kode_company }}" tabindex="-1">

                    <div class="modal-dialog modal-lg modal-dialog-scrollable">

                        <div class="modal-content border-0" style="border-radius:18px;">

                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title">
                                    KLA - {{ $item->company_name }} ({{ $item->kode_company }})
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="alert alert-primary">
                                    <strong>Total Case :</strong> {{ $totalCaseModal }}
                                </div>

                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm table-striped mb-0">
                                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th>No</th>
                                                <th>Case ID</th>
                                                <th>COF</th>
                                                <th>Invoice</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($casesModal as $case)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $case->{$fieldCaseModal} }}</td>
                                                <td>
                                                    <span class="badge bg-info text-dark">{{ $case->upload ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info text-dark">{{ $case->transaksi ?? '-' }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data KLA</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>
