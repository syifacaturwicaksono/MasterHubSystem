{{-- [PARTIAL] bulk.modals.monitoring — bagian dari bulk.blade.php (baris asli 1369-1452) — Modal preview — monitoring lain (WIP, Pending, KCI, Finish Repair) --}}
                {{-- Modal Monitoring (WIP, Pending, KCI, Finish Repair) --}}
                <div class="modal fade" id="modal{{ $item->kode_company }}" tabindex="-1" data-bs-backdrop="static"
                    data-bs-keyboard="false">

                    <div class="modal-dialog modal-lg modal-dialog-scrollable">

                        <div class="modal-content border-0" style="border-radius:18px;">

                            <div class="modal-header bg-dark text-white">

                                <h5 class="modal-title">
                                    Company Code: {{ $item->kode_company }}
                                </h5>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <div class="alert alert-primary">
                                    <strong>Total Case :</strong> {{ $totalCaseModal }}

                                    @if(request('aging_filter'))
                                    | Filter Aging : {{ request('aging_filter') }}
                                    @endif
                                </div>

                                <div class="table-responsive">

                                    <table class="table table-bordered table-sm table-striped">

                                        <thead>

                                            <tr>
                                                <th>No</th>
                                                <th>Case ID</th>
                                                <th>Status</th>
                                                <th>Aging</th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                            @forelse($casesModal as $case)

                                            <tr>

                                                <td>{{ $loop->iteration }}</td>

                                                <td>{{ $case->{$fieldCaseModal} }}</td>

                                                <td>{{ $case->case_status }}</td>

                                                <td>{{ number_format($case->aging, 1) }}</td>

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
