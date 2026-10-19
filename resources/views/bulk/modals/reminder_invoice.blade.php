{{-- [PARTIAL] bulk.modals.reminder_invoice — bagian dari bulk.blade.php (baris asli 992-1070) — Modal preview — jenis M002-Reminder Invoice --}}
                {{-- Modal Reminder Invoice --}}
                <div class="modal fade" id="modal{{ $item->kode_company }}" tabindex="-1">

                    <div class="modal-dialog modal-xl modal-dialog-scrollable">

                        <div class="modal-content">

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    Reminder Invoice - {{ $item->company_name }}
                                </h5>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="alert alert-info">
                                    Total Invoice : <b>{{ $totalCaseModal }}</b>
                                </div>

                                <div class="table-responsive">

                                    <table class="table table-bordered table-striped">

                                        <thead class="table-light">

                                            <tr>
                                                <th>No</th>
                                                <th>Company</th>
                                                <th>Kekurangan Dokumen</th>
                                                <th>Jatuh Tempo</th>
                                                <th>Total</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                            @forelse($casesModal as $case)

                                            <tr>

                                                <td>{{ $loop->iteration }}</td>

                                                <td>{{ $case->partner }}</td>
                                                <td>{{ $case->kekurangan_dokumen }}</td>

                                                <td>{{ $case->jatuh_tempo }}</td>

                                                <td class="text-end">
                                                    {{ number_format($case->total, 0, ',', '.') }}
                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    Tidak ada data
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
