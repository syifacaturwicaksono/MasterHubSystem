{{-- [PARTIAL] template_master.modals.edit_template — bagian dari template_master.blade.php (baris asli 662-918) — Modal Edit Template (di dalam loop @forelse, memakai $item) --}}
                        <!-- MODAL EDIT -->
                        <div
                            class="modal fade"
                            id="editModal{{ $item->id }}"
                            tabindex="-1">

                            <div class="modal-dialog modal-xl">

                                <div class="modal-content">

                                    <!-- ★★★ DIUBAH  (3/5): id="editTemplateForm{{ $item->id }}" baru
                                        ditambahkan (untuk hook JS Summernote per baris). ★★★ -->
                                    <form
                                        id="editTemplateForm{{ $item->id }}"
                                        action="/template-master/update/{{ $item->id }}"
                                        method="POST">

                                        @csrf

                                        <div class="modal-header">

                                            <h5 class="modal-title">

                                                Edit Template

                                            </h5>

                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal">
                                            </button>

                                        </div>


                                        <div class="modal-body">

                                            <div class="mb-3">

                                                <label>

                                                    Jenis Monitoring

                                                </label>

                                                <select
                                                    name="jenis_monitoring"
                                                    class="form-select"
                                                    required>
                                                    @if(in_array(auth()->user()->role, ['admin', 'apo2']))

                                                    <option value="W001-WIP"
                                                        {{ $item->jenis_monitoring=='W001-WIP'  ? 'selected' : '' }}>
                                                        W001-WIP
                                                    </option>

                                                    <option value="W002-TAT14D (Pending case 14 days)"
                                                        {{ $item->jenis_monitoring=='W002-TAT14D (Pending case 14 days)' ? 'selected' : '' }}>
                                                        W002-TAT14D (Pending case 14 days)
                                                    </option>

                                                    <option value="W003-TAT5D (Pending case 5 days)"
                                                        {{ $item->jenis_monitoring=='W003-TAT5D (Pending case 5 days)' ? 'selected' : '' }}>
                                                        W003-TAT5D (Pending case 5 days)
                                                    </option>

                                                    <option value="W004-KCI"
                                                        {{ $item->jenis_monitoring=='W004-KCI' ? 'selected' : '' }}>
                                                        W004-KCI
                                                    </option>

                                                    <option value="W005-FR (Finish Repair)"
                                                        {{ $item->jenis_monitoring=='W005-FR (Finish Repair)' ? 'selected' : '' }}>
                                                        W005-FR (Finish Repair)
                                                    </option>

                                                    <option value="M001-MPR"
                                                        {{ $item->jenis_monitoring=='M001-MPR' ? 'selected' : '' }}>
                                                        M001-MPR
                                                    </option>
                                                    @endif
                                                    @if(in_array(auth()->user()->role, ['admin', 'finance']))
                                                    <option value="M002-Reminder Invoice"
                                                        {{ $item->jenis_monitoring=='M002-Reminder Invoice' ? 'selected' : '' }}>
                                                        M002-Reminder Invoice
                                                    </option>

                                                    <option value="M003-Pembayaran"
                                                        {{ $item->jenis_monitoring=='M003-Pembayaran' ? 'selected' : '' }}>
                                                        M003-Pembayaran
                                                    </option>
                                                    @endif

                                                     @if(in_array(auth()->user()->role, ['admin', 'apo']))
                                                    <option value="M004-PO Labor"
                                                        {{ $item->jenis_monitoring=='M004-PO Labor' ? 'selected' : '' }}>
                                                        M004-PO Labor
                                                    </option>

                                                    @endif
                                                    @if(in_array(auth()->user()->role, ['admin', 'kla']))
                                                    <option value="D001-KLA"
                                                        {{ $item->jenis_monitoring=='D001-KLA' ? 'selected' : '' }}>
                                                        D001-KLA
                                                    </option>

                                                    @endif


                                                </select>

                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kategori</label>

                                                <select name="kategori" class="form-select" required>

                                                    @if(auth()->user()->role == 'admin')
                                                    <option value="apo2" {{ $item->kategori == 'apo2' ? 'selected' : '' }}>
                                                        Apo2
                                                    </option>

                                                    <option value="finance" {{ $item->kategori == 'finance' ? 'selected' : '' }}>
                                                        Finance
                                                    </option>
                                                    <option value="apo" {{ $item->kategori == 'apo' ? 'selected' : '' }}>
                                                        Apo
                                                    </option>
                                                    <option value="kla" {{ $item->kategori == 'kla' ? 'selected' : '' }}>
                                                        KLA
                                                    </option>

                                                    @elseif(auth()->user()->role == 'apo2')

                                                    <option value="apo2" selected>Apo2</option>

                                                    @elseif(auth()->user()->role == 'finance')

                                                    <option value="finance" selected>Finance</option>

                                                    @elseif(auth()->user()->role == 'apo')

                                                    <option value="apo" selected>Apo</option>

                                               
                                                    @elseif(auth()->user()->role == 'kla')

                                                    <option value="kla" selected>KLA</option>

                                                    @endif

                                                </select>
                                            </div>

                                            <div class="mb-3">

                                                <label>

                                                    Nama Template

                                                </label>

                                                <input
                                                    type="text"
                                                    name="nama_template"
                                                    class="form-control"
                                                    value="{{ $item->nama_template }}"
                                                    required>

                                            </div>


                                            <div class="mb-3">

                                                <label>

                                                    Subject

                                                </label>

                                                <input
                                                    type="text"
                                                    name="subject"
                                                    class="form-control"
                                                    value="{{ $item->subject }}"
                                                    required>

                                            </div>


                                            <div class="mb-3">

                                                <label>

                                                    Body

                                                </label>

                                                <!-- ★★★ DIUBAH (4/5): textarea ini sekarang ditumpangi
                                                    Summernote via initSummernote() saat modal dibuka. ★★★ -->
                                                <textarea
                                                    name="body"
                                                    id="body_edit_{{ $item->id }}"
                                                    class="form-control">{{ $item->body }}</textarea>

                                            </div>


                                            <div class="alert alert-info">

                                                <strong>
                                                    Variable tersedia (ketik langsung di editor):
                                                </strong>

                                                <hr>

                                                @{{case_id}}
                                                <br>

                                                @{{company_name}}
                                                <br>

                                                @{{status}}

                                            </div>

                                        </div>


                                        <div class="modal-footer">

                                            <button
                                                type="button"
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal">

                                                Close

                                            </button>

                                            <button
                                                class="btn btn-primary">

                                                Update Template

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>
