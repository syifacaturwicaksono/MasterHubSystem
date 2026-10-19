{{-- [PARTIAL] bulk.modals.pembayaran — bagian dari bulk.blade.php (baris asli 1074-1158) — Modal preview — jenis M003-Pembayaran --}}
                {{-- Modal Pembayaran --}}
                <div class="modal fade" id="modal{{ $item->kode_company }}" tabindex="-1">

                    <div class="modal-dialog modal-xl modal-dialog-scrollable">

                        <div class="modal-content">

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    Pembayaran - {{ $item->company_name }}
                                </h5>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="alert alert-info">
                                    Total Pembayaran : <b>{{ $totalCaseModal }}</b>
                                </div>

                                @if($casesModal->isEmpty())

                                <p class="text-center text-muted">Tidak ada data</p>

                                @else

                                @php
                                    $buktiPembayaranModal = '';

                                    foreach ($casesModal as $c) {
                                        if ($buktiPembayaranModal === '' && trim($c->bukti_pembayaran ?? '') !== '') {
                                            $buktiPembayaranModal = $c->bukti_pembayaran;
                                        }
                                    }
                                @endphp

                                <table class="table table-bordered table-sm mb-1">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Company name</th>
                                            <th>No Rek</th>
                                            <th>Bank</th>
                                            <th>A/N</th>
                                            <th>Keterangan</th>
                                            <th>Jumlah Rp.</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($casesModal as $case)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $case->company_name }}</td>
                                            <td>{{ $case->no_rek }}</td>
                                            <td>{{ $case->bank }}</td>
                                            <td>{{ $case->atas_nama }}</td>
                                            <td>{{ $case->keterangan }}</td>
                                            <td class="text-end">{{ number_format($case->jumlah, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($case->total, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach

                                        @if($buktiPembayaranModal !== '')
                                        <tr>
                                            <td colspan="8" style="background:#f5f5f5; font-size:12px;">
                                                {!! nl2br(e($buktiPembayaranModal)) !!}
                                            </td>
                                        </tr>
                                        @endif

                                    </tbody>
                                </table>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>
