{{-- [PARTIAL] import.sections.rr_history — bagian dari import.blade.php (baris asli 753-778) — Kartu Riwayat RR lintas bulan --}}
        <!-- [DITAMBAHKAN] RIWAYAT RR (LINTAS BULAN) -->
        <div class="up-card">
            <div class="up-card-head">
                <div>
                    <h2 class="up-card-title">Riwayat RR</h2>
                    <p class="up-card-note">
                        Untuk cek RR lintas bulan (maks. 30 hari dari Closed Date case lama ke Received Date case baru). Riwayat terisi otomatis setiap upload PO Labor, jadi data 3 bulan boleh tersimpan.
                        Upload file 3 bulan di sini hanya untuk mengisi riwayat awal &mdash; <strong>tidak masuk BC Email</strong>.
                    </p>
                </div>
            </div>
            <div class="up-card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">File Excel Riwayat</label>
                        <input type="file" id="historyFile" class="form-control" accept=".xlsx,.xls">
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="historyUploadBtn" class="btn up-btn up-btn-ghost">
                            Simpan ke Riwayat
                        </button>
                    </div>
                </div>
                <div id="historyMsg" class="mt-3" style="font-size:13px;"></div>
            </div>
        </div>
