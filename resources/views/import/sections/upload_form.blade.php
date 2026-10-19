{{-- [PARTIAL] import.sections.upload_form — bagian dari import.blade.php (baris asli 247-365) — Form upload Excel (jenis, kode, file, tombol) --}}
            <!-- FORM -->
            <form action="/import" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <p class="mb-4" style="font-size:13.5px;color:#475569;">
                    Contoh format upload file tersedia
                    <a class="up-link"
                        href="https://drive.google.com/drive/folders/1YeCJJaNSWWEibJPIw-_iHghPxt57wgif?usp=drive_link"
                        target="_blank">di sini</a>.
                </p>

                <div class="row">


                    <!-- JENIS -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">
                            Jenis Monitoring
                        </label>

                        <select name="jenis_upload" class="form-select" id="jenisUpload" required>

                            <option value="">-- Pilih Jenis --</option>

                            @if(in_array(auth()->user()->role, ['admin', 'apo2']))
                            <option value="W001-WIP">
                                W001 - WIP
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

                            <option value="W005-FR (Finish Repair)">
                                W005-FR (Finish Repair)
                            </option>
                            <option value="M001-MPR">
                                M001 - Monthly Performance Review (MPR)
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

                            {{-- [DITAMBAHKAN] KLA --}}
                            @if(in_array(auth()->user()->role, ['admin', 'kla']))
                            <option value="D001-KLA">
                                D001-KLA
                            </option>
                            @endif

                            {{-- [DITAMBAHKAN] PO Labor / APO1 --}}
                            @if(in_array(auth()->user()->role, ['admin', 'apo']))
                            <option value="M004-PO Labor">
                                M004-PO Labor
                            </option>
                            @endif


                        </select>
                    </div>

                    <!-- KODE -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">
                            Nama Batch
                        </label>

                        <input type="text" name="kode_upload" id="kodeUpload" class="form-control"
                            placeholder="Contoh: WIP0428" required>


                    </div>

                    <!-- FILE -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">
                            File Excel
                        </label>

                        <input type="file" name="file" class="form-control" id="excelFile" accept=".xlsx,.xls" required>
                    </div>

                </div>

                <!-- [DITAMBAHKAN] Simpan pilihan part per Case ID (PO Labor), dikirim bareng file -->
                <input type="hidden" name="apo_case_overrides" id="apoCaseOverrides" value="">
                <input type="hidden" name="apo_labor_overrides" id="apoLaborOverrides" value="">
                <!-- [DITAMBAHKAN] Keterangan manual per Case ID dari popup review -->
                <input type="hidden" name="apo_keterangan_overrides" id="apoKeteranganOverrides" value="">

                <!-- BUTTON -->
                <div class="up-actions">
                    <button id="uploadSubmitBtn" class="btn up-btn up-btn-primary">
                        Upload &amp; Process
                    </button>

                    <!-- [DITAMBAHKAN] Revisi: buka data PO Labor terakhir di database
                         (belum terkirim) di popup review yang sama, tanpa upload ulang -->
                    <button type="button" id="revisiPoLaborBtn" class="btn up-btn up-btn-ghost"
                        title="Buka data PO Labor terakhir di database untuk direvisi, tanpa upload ulang">
                        Revisi PO Labor
                    </button>
                </div>

            </form>
