{{-- [PARTIAL] bulk.sections.template_footer_panel — bagian dari bulk.blade.php (baris asli 1459-1621) — Panel pilih template email, footer, dan tombol aksi kirim --}}
                <!-- TEMPLATE EMAIL -->
                <div class="card mt-4">
                    <div class="card-body">

                        <h5 class="bc-card-title">
                            Template Email
                        </h5>

                        <div class="row g-3 align-items-end">

                            <!-- TEMPLATE -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Pilih Template
                                </label>

                                <select id="templateSelect" name="template_id" class="form-select">

                                    <option value="">-- Pilih Template --</option>

                                    @foreach($templates as $template)

                                    @if(
                                    (
                                    in_array(auth()->user()->role, ['admin', 'apo2']) &&
                                    in_array($template->jenis_monitoring, [
                                    'W001-WIP',
                                    'W002-TAT14D (Pending case 14 days)',
                                    'W003-TAT5D (Pending case 5 days)',
                                    'W004-KCI',
                                    'W005-FR (Finish Repair)',
                                    'M001-MPR'
                                    ])
                                    )
                                    ||
                                    (
                                    in_array(auth()->user()->role, ['admin', 'finance']) &&
                                    in_array($template->jenis_monitoring, [
                                    'M002-Reminder Invoice',
                                    'M003-Pembayaran'
                                    ])
                                    )
                                    ||
                                    (
                                    in_array(auth()->user()->role, ['admin', 'kla']) &&
                                    $template->jenis_monitoring == 'D001-KLA'
                                    )
                                    ||
                                    (
                                    in_array(auth()->user()->role, ['admin', 'apo']) &&
                                    $template->jenis_monitoring == 'M004-PO Labor'
                                    )
                                    )

                                    <option value="{{ $template->id }}" data-monitoring="{{ $template->jenis_monitoring }}"
                                        data-subject="{{ $template->subject }}" data-body="{{ $template->body }}"
                                        data-nama="{{ $template->nama_template }}">

                                        {{ $template->nama_template }}

                                    </option>

                                    @endif

                                    @endforeach

                                </select>
                            </div>

                            <!-- EDIT -->
                            <div class="col-md-3">
                                <button type="button" class="btn btn-warning w-100" id="btnEditTemplate">

                                    Edit Template

                                </button>
                            </div>

                        </div>

                        <hr class="my-4">

                        <!-- FOOTER MASTER -->
                        <div class="row g-3 align-items-end">

                            <!-- FOOTER -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Pilih Footer
                                </label>

                                <select id="footerSelect" name="footer_id" class="form-select">

                                    <option value="">
                                        -- Pilih Footer --
                                    </option>

                                    @foreach($footers as $footer)

                                    <option value="{{ $footer->id }}" data-footer="{{ htmlentities($footer->footer_html) }}"
                                        data-name="{{ $footer->footer_name }}" {{ $footer->id == 1 ? 'selected' : '' }}>

                                        {{ $footer->footer_name }}

                                    </option>

                                    @endforeach

                                </select>

                            </div>

                            <!-- EDIT -->
                            <div class="col-md-3">

                                <button type="button" class="btn btn-warning w-100" id="btnEditFooter">

                                    Edit Footer

                                </button>

                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="mt-3">

                            <label class="form-label fw-semibold">
                                Attachment
                            </label>

                            <input type="file" name="attachments[]" class="form-control" multiple>

                            <small class="text-muted">
                                Bisa upload JPG, PNG, PDF, XLSX, DOCX
                            </small>

                        </div>

                        <!-- ACTION BUTTON -->
                        <div class="d-flex gap-2 mt-4">

                            <button type="submit" formaction="{{ route('email.preview') }}" formtarget="_blank"
                                class="btn btn-info">

                                Preview Email

                            </button>

                            <!-- Input hidden diletakkan SEBELUM tombol -->
                            <input type="hidden" name="jenis_upload" value="{{ request('jenis_upload') }}">

                            <button type="submit" formaction="{{ route('email.send') }}" class="btn btn-primary">
                                Kirim Bulk Email
                            </button>

                        </div>

                    </div>
                </div>
