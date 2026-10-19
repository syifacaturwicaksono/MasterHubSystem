{{-- [PARTIAL] import.sections.part_review_modal — bagian dari import.blade.php (baris asli 367-575) — Modal review Part per Case ID (Langkah 1 RR & Langkah 2 Part) --}}
            <!-- [DIUBAH] Modal review part per Case ID (khusus PO Labor) — sekarang nampilin
                 SEMUA case yang punya part di sheet Parts, bukan cuma yang belum dikenal -->
            <div class="modal" id="partClassificationModal" tabindex="-1" style="background:rgba(0,0,0,0.5);">
                <!-- [DIUBAH] Fullscreen + 2 langkah dalam 1 halaman (seperti Google Form):
                     Langkah 1 = Review RR (validasi dulu), Langkah 2 = Review Part & Kategori
                     (RR sudah ditandai otomatis dari hasil Langkah 1).
                     Next / Kembali cuma menukar tampilan, tanpa refresh, tanpa fetch
                     (RR dihitung langsung di browser dari sheet yang sama). -->
                <div class="modal-dialog modal-fullscreen">

                    <!-- ================= LANGKAH 1 (RR) ================= -->
                    <div class="modal-content" id="reviewStep1">
                        <div class="modal-header py-2" style="background:#cff4fc;">
                            <h5 class="modal-title">
                                <span class="pc-title-ico"><svg class="" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg></span>
                                <span>Review RR (Re Repair)</span>
                                <span class="badge text-bg-dark ms-2 align-middle">Langkah 1 dari 2</span>
                            </h5>
                            <button type="button" class="btn-close" id="closePartClassificationBtn" aria-label="Close" title="Batal, file yang diupload salah"></button>
                        </div>

                        <div class="modal-body">
                            <div class="pc-callout"> Rule RR: dalam 1 file, Case ID dengan <b>Serial No yang sama</b> dianggap RR. Case dari bulan sebelumnya
                                (baris berlabel <b>Riwayat</b>) juga dihitung RR kalau jaraknya <b>maksimal 30 hari</b>
                                dari Closed Date case lama ke Received Date case baru.
                                Case pertama per Serial No tetap dapat Labor normal (bukan RR).
                                Kalau case pertama <b>Cancel</b> dan case berikutnya tidak Cancel, pilih <b>Dapat Labor</b>
                                di kolom Keterangan (otomatis terisi untuk case pertama yang tidak Cancel setelah Cancel) —
                                di Langkah 2 tetap ditandai RR (hijau) tapi dapat Labor.
                                Kolom Keterangan bisa dipilih dari saran <b>atau diketik manual</b> kalau opsinya tidak ada.
                                Cek dulu di sini sebelum lanjut ke Review Part &amp; Kategori — data belum tersimpan.
                            </div>

                            <div class="part-review-table-wrapper" id="rrTableWrapper">
                                <table class="table table-bordered align-middle mb-0" id="rrTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Serial No</th>
                                            <th>Case ID</th>
                                            <th>Company</th>
                                            <th style="min-width: 90px;">Product Tower</th>
                                            <th>Product Name</th>
                                            <th style="min-width: 150px;">Case Status</th>
                                            <th style="min-width: 130px;">Warranty Status</th>
                                            <th>Repair Action</th>
                                            <th style="min-width: 220px;">Keterangan</th>
                                        </tr>
                                    </thead>

                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="alert alert-secondary d-none mb-0" id="rrEmptyMsg">
                                Tidak ada RR terdeteksi di file ini.
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-end align-items-center">
                            <!-- Link info -->
                            <a href="https://docs.google.com/spreadsheets/d/1zkiBC75GvwswCseCPjTX8rk_cQE84bVE/edit?usp=drive_link&ouid=109389621746043914440&rtpof=true&rtpof=true&sd=true" target="_blank" rel="noopener" class="pc-help" title="Info / panduan">
                                <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            </a>

                            <!-- Tombol Next -->
                            <button type="button" class="btn btn-success px-4" id="nextToRrBtn"> Next <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg> </button>
                        </div>
                    </div>

                    <!-- ================= LANGKAH 2 (Review Part & Kategori) ================= -->
                    <div class="modal-content d-none" id="reviewStep2">
                        <div class="modal-header bg-warning py-2">
                            <h5 class="modal-title">
                                <span class="pc-title-ico"><svg class="" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94z"/></svg></span>
                                <span>Review Part &amp; Kategori per Case ID</span>
                                <span class="badge text-bg-dark ms-2 align-middle">Langkah 2 dari 2</span>
                            </h5>
                        </div>

                        <div class="modal-body">

                            <!-- [DIUBAH] Toolbar ringkas 1 area: tombol Cara penggunaan (panel
                                 melayang, tidak mendorong tabel), filter tower, cari Serial No,
                                 arti warna, dan total case. Supaya ruang tabel lebih luas. -->
                            <div class="position-relative" id="reviewToolbarWrap">

                                <div class="review-toolbar d-flex flex-wrap align-items-center gap-2 mb-2">

                                    <button type="button" class="btn btn-sm btn-outline-primary" id="reviewGuideBtn"> <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Cara penggunaan </button>

                                    <select id="reviewTowerFilter" class="form-select form-select-sm" style="width:auto;">
                                        <option value="">Semua Tower</option>
                                        <option value="IPG">IPG</option>
                                        <option value="PSG">PSG</option>
                                    </select>

                                    <select id="reviewLaborFilter" class="form-select form-select-sm" style="width:auto;"
                                        title="Filter berdasarkan nominal Labor">
                                        <option value="">Semua Labor</option>
                                        <option value="none">Tanpa Labor (-)</option>
                                        <option value="50000">Rp 50.000</option>
                                        <option value="90000">Rp 90.000</option>
                                        <option value="100000">Rp 100.000</option>
                                        <option value="170000">Rp 170.000</option>
                                    </select>
                                    <!-- [DIUBAH] Filter Company Name: dropdown biasa (sama seperti filter lain),
                                         daftar company diisi dari data dan selalu lengkap saat dibuka -->
                                    <select id="reviewCompanyFilter" class="form-select form-select-sm"
                                        style="width:190px;" title="Filter berdasarkan Company Name">
                                        <option value="">Semua Company</option>
                                    </select>
                                    <!-- [DITAMBAHKAN] Filter Case Type (opsi diisi dari data) -->
                                    <select id="reviewCaseTypeFilter" class="form-select form-select-sm" style="width:auto;"
                                        title="Filter berdasarkan Case Type">
                                        <option value="">Semua Case Type</option>
                                    </select>
                                    <!-- [DITAMBAHKAN] Filter Warranty Status (opsi diisi dari data) -->
                                    <select id="reviewWarrantyFilter" class="form-select form-select-sm" style="width:auto;"
                                        title="Filter berdasarkan Warranty Status">
                                        <option value="">Semua Warranty</option>
                                    </select>

                                    <div class="pc-search">
                                        <svg class="pc-ico pc-search-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        <input type="search" id="reviewSearchInput" class="form-control form-control-sm"
                                            placeholder="Cari Serial No / Case ID">
                                    </div>

                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="reviewFilterReset"> <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg> Reset </button>

                                    <!-- Arti warna baris -->
                                    <div class="review-legend small ms-lg-2">
                                        <span class="legend-item"><i class="legend-dot" style="background:#e2e8f0"></i>Cancel <b id="legendCountCancel">(0)</b></span>
                                        <span class="legend-item"><i class="legend-dot" style="background:#dbeafe"></i>IPG <b id="legendCountIpg">(0)</b></span>
                                        <span class="legend-item"><i class="legend-dot" style="background:#ffffff"></i>PSG <b id="legendCountPsg">(0)</b></span>
                                        <span class="legend-item"><i class="legend-dot" style="background:#dcfce7"></i>RR <b id="legendCountRr">(0)</b></span>
                                        <span class="legend-item"><i class="legend-dot" style="background:#fef9c3"></i>Close Repair tanpa part <b id="legendCountKuning">(0)</b></span>
                                        <span class="legend-item"><i class="legend-dot" style="background:#fee2e2"></i>Part Baru <b id="legendCountPartBaru">(0)</b></span>
                                    </div>

                                    <small class="text-muted fw-bold ms-auto" id="totalPartSheetText">
                                        Total Case : 0
                                    </small>
                                </div>

                                <!-- Panel Cara penggunaan (muncul saat tombol diklik) -->
                                <div class="review-guide-panel d-none" id="reviewGuidePanel">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2"><svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Cara penggunaan</div>
                                        <button type="button" class="btn-close" id="reviewGuideClose" aria-label="Tutup"></button>
                                    </div>
                                    <div class="review-guide small text-muted">
                                        <ol>
                                            <li>Cek tiap Case ID. Kalau part lebih dari 1, pilih lewat dropdown <b>HP Part No</b>.</li>
                                            <li>Cek/ganti <b>Kategori</b>. Labor otomatis mengikuti kategori + Product Tower. Pilih <b>Void Case</b> kalau case tidak ditagih (tanpa Labor).</li>
                                            <li>Gunakan filter <b>IPG/PSG</b> dan kolom cari <b>Serial No</b> di atas tabel. Klik <b>Case ID</b> untuk membuka halaman service.</li>
                                            <li>RR (hijau) sudah ditandai dari hasil Langkah 1.</li>
                                            <li><b>Simpan</b> = simpan ke database. <b>Export</b> = unduh tanpa simpan. <b>Simpan &amp; Export</b> = keduanya.</li>
                                        </ol>
                                    </div>
                                </div>

                            </div>

                            <div class="part-review-table-wrapper">
                                <table class="table table-bordered align-middle mb-0" id="partClassificationTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 170px;">Company Name</th>
                                            <th>Case ID</th>
                                            <th style="min-width: 150px;">Case Status</th>
                                            <th style="min-width: 130px;">Warranty Status</th>
                                            <th style="min-width: 90px;">Product Tower</th>
                                            <th style="min-width: 90px;">Case Type</th>
                                            <th class="text-center">Total Part</th>
                                            <th>HP Part No</th>
                                            <th>Part Name</th>
                                            <th style="min-width: 250px;">Repair Action</th>
                                            <th style="min-width: 190px;">Kategori</th>
                                            <th class="text-end">Labor</th>
                                            <th style="min-width: 200px;">Keterangan</th>
                                        </tr>
                                    </thead>

                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <!-- Link info -->
                            <a href="https://docs.google.com/spreadsheets/d/1zkiBC75GvwswCseCPjTX8rk_cQE84bVE/edit?usp=drive_link&ouid=109389621746043914440&rtpof=true&rtpof=true&sd=true" target="_blank" rel="noopener" class="pc-help" title="Info / panduan">
                                <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            </a>
                            <!-- [DITAMBAHKAN] Total Labor (ikut filter yang sedang aktif) -->
                            <div class="pc-total-labor" id="reviewTotalLabor">
                                <span class="pc-total-labor-label" id="reviewTotalLaborLabel">Total Labor (semua)</span>
                                <b class="pc-total-labor-value" id="reviewTotalLaborValue">Rp 0</b>
                            </div>
                            <button type="button" class="btn btn-outline-dark" id="rrBackBtn"> <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg> Kembali </button>
                            <button type="button" class="btn btn-outline-secondary" id="rrExportBtn"> <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg> Export </button>
                            <button type="button" class="btn btn-primary" id="savePartClassificationBtn"> <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg> Simpan </button>
                            <button type="button" class="btn btn-success" id="rrSaveExportBtn"> <svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg><svg class="pc-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg> Simpan &amp; Export </button>
                        </div>
                    </div>

                </div>

            </div>
