{{-- [PARTIAL] bulk.modals.po_labor — bagian dari bulk.blade.php (baris asli 1224-1365) — Modal preview — jenis M004-PO Labor --}}
                {{-- ★★★ BARU: Modal PO Labor (APO) ★★★ --}}
                <div class="modal fade" id="modal{{ $item->kode_company }}" tabindex="-1">

                    <div class="modal-dialog modal-dialog-scrollable" style="max-width: 96vw;">

                        <div class="modal-content border-0" style="border-radius:18px;">

                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title">
                                    PO Labor (APO) - {{ $item->kode_company }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            @php
                            $totalLabor = $casesModal->sum(function ($case) {
                            return (float) str_replace(',', '', $case->labor ?? 0);
                            });
                            @endphp

                            <div class="modal-body p-0">

                                <!-- Area tabel yang bisa di-scroll -->
                                <div class="table-responsive"
                                    style="max-height: 400px; overflow-y: auto; overflow-x: auto;">

                                    <table class="table table-bordered table-sm table-striped mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">

                                        <colgroup>
                                            <col style="width: 3%;">
                                            <col style="width: 8%;">
                                            <col style="width: 8%;">
                                            <col style="width: 12%;">
                                            <col style="width: 9%;">
                                            <col style="width: 15%;">
                                            <col style="width: 10%;">
                                            <col style="width: 7%;">
                                            <col style="width: 14%;">
                                            <col style="width: 14%;">
                                        </colgroup>

                                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th>No</th>
                                                <th>Case ID</th>
                                                <th>Warranty Status</th>
                                                <th>Product Name</th>
                                                <th>Case Status</th>
                                                <th>Part Name</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Labor</th>
                                                <th>Repair Action</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse($casesModal as $case)
                                            <tr id="apo-row-{{ $case->id }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td style="overflow-wrap: break-word;">{{ $case->{$fieldCaseModal} }}</td>
                                                <td style="overflow-wrap: break-word;">{{ $case->warranty_status ?? '-' }}</td>
                                                <td style="overflow-wrap: break-word;">{{ $case->product_name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge bg-warning text-dark" style="white-space: normal;">
                                                        {{ $case->case_status ?? '-' }}
                                                    </span>
                                                </td>
                                                <td style="overflow-wrap: break-word;">{{ $case->part_name ?? '-' }}</td>
                                                <td class="apo-kategori-text" style="overflow-wrap: break-word;">{{ $case->part_category ?? '-' }}</td>
                                                <td class="text-end apo-labor-text">
                                                    {{ number_format((float) ($case->labor ?? 0), 0, ',', '.') }}
                                                </td>
                                                @php
                                                // Repair Action: kolom repair_action; kalau kosong (data lama) pakai isi keterangan lama.
                                                $repairActionText = trim((string) ($case->repair_action ?? '')) !== ''
                                                    ? trim((string) $case->repair_action)
                                                    : trim((string) ($case->keterangan ?? ''));

                                                // Keterangan: isian dari popup review (RR / manual). Kalau isinya sama
                                                // dengan Repair Action (data lama), tidak ditampilkan dobel.
                                                $keteranganText = trim((string) ($case->keterangan ?? ''));
                                                if ($keteranganText === $repairActionText) {
                                                    $keteranganText = '';
                                                }

                                                // [DIUBAH] Disamakan dengan Export di popup Review Part & Excel yang
                                                // dikirim (lihat EmailController::normalizePoLaborKeterangan):
                                                // hanya dropdown RR + isian manual; tanpa "RR -", tanpa
                                                // "Close Repair tanpa part", tanpa "Dapat Labor"; case Cancel
                                                // diberi awalan "Cancel".
                                                if ($keteranganText !== '') {
                                                    $keteranganText = preg_replace('/\[\[CAT:[^\]]+\]\]/', '', $keteranganText);
                                                    $keteranganText = preg_replace('/^RR\s*-\s*/i', '', $keteranganText);
                                                    if (preg_match('/^RR$/i', trim($keteranganText))) {
                                                        $keteranganText = '';
                                                    }
                                                    $keteranganText = preg_replace('/^Close Repair tanpa part\s*(-\s*)?/i', '', $keteranganText);
                                                    $keteranganText = preg_replace('/\bVoid Case\b\s*(-\s*)?/i', '', $keteranganText);
                                                    $keteranganText = preg_replace('/dapat labor/i', '', $keteranganText);
                                                    $keteranganText = preg_replace('/^[\s\-–,;:]+|[\s\-–,;:]+$/u', '', $keteranganText);
                                                    $keteranganText = trim(preg_replace('/\s{2,}/', ' ', $keteranganText));
                                                }

                                                if (str_contains(strtolower((string) ($case->case_status ?? '')), 'cancel')
                                                    && !preg_match('/cancel/i', $keteranganText)) {
                                                    $keteranganText = $keteranganText !== '' ? 'Cancel - ' . $keteranganText : 'Cancel';
                                                }
                                                @endphp
                                                <td style="white-space: normal; overflow-wrap: break-word;">
                                                    {{ $repairActionText !== '' ? $repairActionText : '-' }}
                                                </td>
                                                <td style="white-space: normal; overflow-wrap: break-word;">
                                                    {{ $keteranganText !== '' ? $keteranganText : '-' }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">Tidak ada data APO</td>
                                            </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>

                                <!-- Total tetap di bawah modal -->
                                <div class="border-top bg-light px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center fw-bold">
                                        <span>Total Labor</span>
                                        <span class="apo-total-labor-value">Rp {{ number_format($totalLabor, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>


                </div>
