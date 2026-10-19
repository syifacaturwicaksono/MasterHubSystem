{{-- [PARTIAL] template_master.sections.header_add_modal — bagian dari template_master.blade.php (baris asli 253-489) — Judul, tombol tambah, dan modal Tambah Template --}}
    <!-- TITLE -->
    <div class="tm-header">

        <div>

            <div class="tm-eyebrow">Master Data</div>

            <h1 class="tm-title">Master Template Email</h1>

            <p class="tm-subtitle">
                Kelola template email otomatis
            </p>

        </div>

        <!-- ADD -->
        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addTemplateModal">

            Add Template

        </button>

    </div>


    <!-- MODAL ADD -->
    <div
        class="modal fade"
        id="addTemplateModal"
        tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <!-- ★★★ DIUBAH  (1/5): id="addTemplateForm" baru ditambahkan
                    (untuk hook JS Summernote di bawah). ★★★ -->
                <form
                    id="addTemplateForm"
                    action="/template-master/store"
                    method="POST">

                    @csrf

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Tambah Template
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <!-- NAMA -->
                        <div class="mb-3">

                            <label class="form-label">

                                jenis Monitoring

                            </label>

                            <select
                                name="jenis_monitoring"
                                class="form-select"
                                required>

                                <option value="">-- Pilih Monitoring --</option>

                                @if(in_array(auth()->user()->role, ['admin', 'apo2']))
                                <option value="W001-WIP">W001-WIP</option>
                                <option value="W002-TAT14D (Pending case 14 days)">W002-TAT14D (Pending case 14 days)</option>
                                <option value="W003-TAT5D (Pending case 5 days)">W003-TAT5D (Pending case 5 days)</option>
                                <option value="W004-KCI">W004-KCI</option>
                                <option value="W005-FR (Finish Repair)">W005-FR (Finish Repair)</option>
                                <option value="M001-MPR">M001-MPR</option>
                                
                                @endif

                                @if(in_array(auth()->user()->role, ['admin', 'finance']))
                                <option value="M002-Reminder Invoice">M002-Reminder Invoice</option>
                                <option value="M003-Pembayaran">M003-Pembayaran</option>
                                @endif
                                @if(in_array(auth()->user()->role, ['admin', 'apo']))
                                <option value="M004-PO Labor">M004-PO Labor</option>
                                
                                @endif
                                @if(in_array(auth()->user()->role, ['admin', 'kla']))
                                <option value="D001-KLA">D001-KLA</option>
                                
                                @endif
                            </select>


                        </div>


                        <div class="mb-3">

                            <label class="form-label">

                                Nama Template

                            </label>

                            <input
                                type="text"
                                name="nama_template"
                                class="form-control"
                                placeholder="Contoh: Reminder Repair"
                                required>

                        </div>
                        <select name="kategori" class="form-select" required>

                            @if(auth()->user()->role == 'admin')

                            <option value="apo2">Apo2</option>
                            <option value="finance">Finance</option>
                             <option value="Apo">Apo</option>
                             <option value="kla">KLA</option>

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

                        <!-- SUBJECT -->
                        <div class="mb-3">

                            <label class="form-label">

                                Subject Email

                            </label>

                            <input
                                type="text"
                                name="subject"
                                class="form-control"
                                placeholder="Masukkan subject"
                                required>

                        </div>


                        <!-- BODY -->
                        <div class="mb-3">

                            <label class="form-label">

                                Isi Template

                            </label>

                            <!-- ★★★ DIUBAH  (2/5): textarea ini sekarang ditumpangi Summernote,
                                lihat initSummernote('#body_add') di script bawah. Atribut/nama tidak berubah. ★★★ -->
                            <textarea
                                name="body"
                                id="body_add"
                                class="form-control"
                                placeholder="Isi template email..."></textarea>

                        </div>

                        <!-- VARIABLE -->
                        <div class="alert alert-info">

                            <strong>
                                Variable tersedia (ketik langsung di editor):
                            </strong>

                            <hr>

                            @{{case_id}} → Case ID
                            <br>

                            @{{company_name}} → Nama Company
                            <br>

                            @{{status}} → Status Case

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

                            Save Template

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
