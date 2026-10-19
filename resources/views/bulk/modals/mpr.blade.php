{{-- [PARTIAL] bulk.modals.mpr — bagian dari bulk.blade.php (baris asli 917-988) — Modal preview — jenis M001-MPR --}}
                {{-- Modal MPR --}}
                <div class="modal fade" id="modal{{ $item->kode_company }}" tabindex="-1">

                    <div class="modal-dialog modal-xl modal-dialog-scrollable">

                        <div class="modal-content border-0" style="border-radius:18px;">

                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title">
                                    {{ strtoupper($item->company_name ?? $item->kode_company) }} ({{ $item->kode_company }})
                                </h5>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="table-responsive">

                                    <table class="table table-bordered table-sm text-center align-middle">

                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Case IPG</th>
                                                <th>Case PSG</th>
                                                <th>Total Case</th>
                                                <th>TAT</th>
                                                <th>TAT w/o BO</th>
                                                <th>OLA</th>
                                                <th>RRP</th>
                                                <th>RRP %</th>
                                                <th>WIP</th>
                                                <th>Unupdate WIP</th>
                                                <th>Latest Aging</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>{{ $item->case_ipg ?? 0 }}</td>
                                                <td>{{ $item->case_psg ?? 0 }}</td>
                                                <td>{{ $item->total_case ?? 0 }}</td>
                                                <td>{{ $item->tat ?? '-' }}</td>
                                                <td>{{ $item->tat_wo_bo ?? '-' }}</td>
                                                <td>{{ $item->ola ?? '-' }}</td>
                                                <td>{{ $item->rrp ?? '-' }}</td>
                                                <td>{{ $item->rrp_percent ?? '-' }}</td>
                                                <td>{{ $item->wip ?? '-' }}</td>
                                                <td>{{ $item->unupdate_wip ?? '-' }}</td>
                                                <td>{{ $item->latest_aging ?? '-' }}</td>
                                            </tr>
                                        </tbody>

                                    </table>

                                </div>

                                @if(!empty($item->catatan))
                                <div class="alert alert-secondary mt-2" style="white-space:pre-line;">
                                    {{ $item->catatan }}
                                </div>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>
