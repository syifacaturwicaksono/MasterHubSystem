{{-- [PARTIAL] bulk.modals.edit_template — bagian dari bulk.blade.php (baris asli 1791-1929) — Modal edit template email --}}
    <!-- MODAL EDIT TEMPLATE -->
    <div class="modal fade" id="editTemplateModal" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <form method="POST" id="editTemplateForm">

                    @csrf
                    @method('PUT')

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Edit Template
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Jenis Monitoring
                            </label>

                            <select name="jenis_monitoring" id="edit_jenis_monitoring" class="form-select" required>

                                <option value="">
                                    -- Pilih Jenis Monitoring --
                                </option>
                                @if(in_array(auth()->user()->role, ['admin', 'apo2']))
                                <option value="W001-WIP">
                                    W001-WIP
                                </option>

                                <option value="W002-TAT14D (Pending case 14 days)">
                                    W002-TAT14D (Pending case 14 days)
                                </option>

                                <option value="W003-TAT5D (Pending case 5 days)">
                                    W003-TAT5D (Pending case 5 days)
                                </option>

                                <option value="W004-KCI">
                                    W004-KCI
                                </option>

                                <option value="W005-Finish Repair">
                                    W005-Finish Repair
                                </option>

                                <option value="M001-MPR">
                                    M001-MPR
                                </option>
                                @endif

                                @if(in_array(auth()->user()->role, ['admin', 'finance']))

                                <option value="M002-Reminder Invoice">
                                    M002-Reminder Invoice
                                </option>

                                <option value="M003-Pembayaran">
                                    M003-Pembayaran
                                </option>
                                @endif

                                {{-- ★★★ BARU: KLA di-disable dulu, konsisten dengan dropdown filter di atas ★★★ --}}
                                @if(in_array(auth()->user()->role, ['admin', 'kla']))
                                <option value="D001-KLA" disabled>
                                    D001-KLA
                                </option>
                                @endif

                                @if(in_array(auth()->user()->role, ['admin', 'apo']))
                                <option value="M004-PO Labor">
                                    M004-PO Labor
                                </option>
                                @endif

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Nama Template
                            </label>

                            <input type="text" name="nama_template" id="edit_nama_template" class="form-control">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Subject
                            </label>

                            <input type="text" name="subject" id="edit_subject" class="form-control">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Body Email
                            </label>

                            <textarea name="body" id="edit_body" rows="15" class="form-control"></textarea>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-success">

                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
