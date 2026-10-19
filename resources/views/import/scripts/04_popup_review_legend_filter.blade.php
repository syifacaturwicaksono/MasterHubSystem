{{-- [PARTIAL] import.scripts.04_popup_review_legend_filter — bagian dari import.blade.php (baris asli 2843-3325) — showPartClassificationPopup() bagian 3/5: legend warna, filter, total labor, tampilkan modal — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                // ========================================================
                // [DITAMBAHKAN] HITUNG TOTAL PER WARNA & UPDATE LEGEND
                // Dipanggil sekali di awal, dan dipanggil ulang tiap kali
                // status "Part Baru" berubah (lihat updateUnknownHighlight).
                // ========================================================

                function updateLegendCounts() {

                    let countCancel = 0;
                    let countRr = 0;
                    let countPartBaru = 0;
                    let countKuning = 0;
                    let countIpg = 0;
                    let countPsg = 0;

                    const rows = tbody.querySelectorAll('tr');

                    // Urutan cek = urutan prioritas warna di CSS
                    rows.forEach(tr => {

                        if (tr.classList.contains('row-cancel')) {
                            countCancel++;
                        } else if (tr.classList.contains('table-success')) {
                            countRr++;
                        } else if (tr.classList.contains('table-danger')) {
                            countPartBaru++;
                        } else if (tr.classList.contains('table-warning')) {
                            countKuning++;
                        } else if (tr.classList.contains('row-ipg')) {
                            countIpg++;
                        } else {
                            countPsg++;
                        }
                    });

                    document.getElementById('legendCountCancel').innerText = `(${countCancel})`;
                    document.getElementById('legendCountRr').innerText = `(${countRr})`;
                    document.getElementById('legendCountIpg').innerText = `(${countIpg})`;
                    document.getElementById('legendCountPsg').innerText = `(${countPsg})`;
                    document.getElementById('legendCountPartBaru').innerText = `(${countPartBaru})`;
                    document.getElementById('legendCountKuning').innerText = `(${countKuning})`;

                    // Total Case = jumlah baris yang ada di tabel
                    document.getElementById('totalPartSheetText').innerText =
                        'Total Case : ' + rows.length;
                }

                updateLegendCounts();


                // ========================================================
                // [DITAMBAHKAN] KELOMPOKKAN BARIS BERDASARKAN WARNA
                // Urutan dari atas: Merah (Part Baru) -> Hijau (RR) ->
                // Kuning (Close Repair tanpa part) -> Biru (IPG) ->
                // Putih (PSG) -> Abu-abu (Cancel). Di dalam satu warna,
                // urutan asli dipertahankan. Hanya diurutkan sekali saat
                // popup dibuka (baris tidak loncat saat user ganti part).
                // Aman karena hasil simpan dibaca lewat tr.dataset.index.
                // ========================================================

                function getDisplayedColorRank(tr) {

                    const c = tr.classList;

                    // Warna yang benar-benar tampil (prioritas sama seperti CSS)
                    if (c.contains('row-cancel')) return 5; // abu-abu
                    if (c.contains('table-success')) return 1; // hijau RR
                    if (c.contains('table-danger')) return 0; // merah Part Baru
                    if (c.contains('table-warning')) return 2; // kuning
                    if (c.contains('row-ipg')) return 3; // biru IPG

                    return 4; // putih PSG
                }

                (function sortRowsByColor() {

                    const sorted = Array.from(tbody.querySelectorAll('tr'))
                        .sort((a, b) => {

                            const diff =
                                getDisplayedColorRank(a) -
                                getDisplayedColorRank(b);

                            if (diff !== 0) {
                                return diff;
                            }

                            return (
                                parseInt(a.dataset.index, 10) -
                                parseInt(b.dataset.index, 10)
                            );
                        });

                    sorted.forEach(tr => tbody.appendChild(tr));
                })();


                // ========================================================
                // [DITAMBAHKAN] FILTER TOWER (IPG/PSG) + CARI SERIAL NO / CASE ID
                // Cuma menyembunyikan baris (display:none). Simpan & Export
                // tetap membaca SEMUA baris, jadi filter tidak mengubah hasil.
                // ========================================================

                // [DITAMBAHKAN] Tombol "Cara penggunaan" (panel melayang)
            const guideBtnEl = document.getElementById('reviewGuideBtn');
            const guidePanelEl = document.getElementById('reviewGuidePanel');

            if (guideBtnEl && guidePanelEl) {

                guidePanelEl.classList.add('d-none');

                guideBtnEl.onclick = function (e) {
                    e.stopPropagation();
                    guidePanelEl.classList.toggle('d-none');
                };

                document.getElementById('reviewGuideClose').onclick = function () {
                    guidePanelEl.classList.add('d-none');
                };

                // klik di luar panel = tutup
                document.getElementById('reviewStep2').onclick = function (e) {
                    if (
                        !guidePanelEl.contains(e.target) &&
                        e.target !== guideBtnEl
                    ) {
                        guidePanelEl.classList.add('d-none');
                    }
                };
            }

            const towerFilterEl = document.getElementById('reviewTowerFilter');
            const laborFilterEl = document.getElementById('reviewLaborFilter');
            const warrantyFilterEl = document.getElementById('reviewWarrantyFilter');
            const caseTypeFilterEl = document.getElementById('reviewCaseTypeFilter');
            const companyFilterEl = document.getElementById('reviewCompanyFilter');
            

            // [DITAMBAHKAN] Nominal Labor yang sedang tampil di sel Labor baris ini
            // (null = "-" / tanpa Labor).
            function parseLaborCell(tr) {

                const text = String(
                    tr.querySelector('.apo-review-labor')?.innerText ?? ''
                ).trim();

                if (!/\d/.test(text)) {
                    return null;
                }

                return Number(text.replace(/\D/g, ''));
            }

            // [DITAMBAHKAN] Opsi filter Labor dibangun dari nominal yang benar-benar
            // ada di tabel (termasuk yang berubah karena ganti kategori / Dapat Labor).
            function rebuildLaborFilterOptions() {

                if (!laborFilterEl) {
                    return;
                }

                const current = laborFilterEl.value;
                const values = new Set();

                tbody.querySelectorAll('tr').forEach(tr => {

                    const v = parseLaborCell(tr);

                    if (v !== null) {
                        values.add(v);
                    }
                });

                if (current && current !== 'none') {
                    values.add(Number(current));
                }

                const sorted = Array.from(values).sort((a, b) => a - b);

                laborFilterEl.innerHTML =
                    '<option value="">Semua Labor</option>' +
                    '<option value="none">Tanpa Labor (-)</option>' +
                    sorted.map(v =>
                        `<option value="${v}">Rp ${v.toLocaleString('id-ID')}</option>`
                    ).join('');

                laborFilterEl.value = current;
            }
                const searchInputEl = document.getElementById('reviewSearchInput');
                const filterResetEl = document.getElementById('reviewFilterReset');

                // [DITAMBAHKAN] Total Labor dari baris yang sedang tampil.
                // Default = semua case; kalau ada filter, ikut terfilter.
                // Kalau filter Company diisi, label memakai nama company-nya.
                let reviewCompanyNames = [];

                function refreshTotalLabor() {
                    const valueEl = document.getElementById('reviewTotalLaborValue');
                    const labelEl = document.getElementById('reviewTotalLaborLabel');
                    if (!valueEl || !labelEl) {
                        return;
                    }

                    let total = 0;
                    tbody.querySelectorAll('tr').forEach(tr => {
                        if (tr.style.display === 'none') {
                            return;
                        }
                        const v = parseLaborCell(tr);
                        if (v !== null) {
                            total += v;
                        }
                    });
                    valueEl.innerText = 'Rp ' + total.toLocaleString('id-ID');

                    const companyText = (companyFilterEl?.value || '').trim();
                    const otherActive = !!(
                        (towerFilterEl?.value || '') ||
                        (laborFilterEl?.value || '') ||
                        (warrantyFilterEl?.value || '') ||
                        (caseTypeFilterEl?.value || '') ||
                        (searchInputEl?.value || '').trim()
                    );

                    let label;
                    if (companyText) {
                        const exact = reviewCompanyNames.find(
                            n => n.toLowerCase() === companyText.toLowerCase()
                        );
                        label = 'Total Labor - ' + (exact || companyText) +
                            (otherActive ? ' (terfilter)' : '');
                    } else {
                        label = otherActive ? 'Total Labor (terfilter)' : 'Total Labor (semua)';
                    }
                    labelEl.innerText = label;
                    labelEl.title = label;
                }

                function applyReviewFilter() {

                    const tower = (towerFilterEl?.value || '').trim().toUpperCase();
                    const query = (searchInputEl?.value || '').trim().toLowerCase();
                    const laborFilter = laborFilterEl?.value || '';
                    const warrantyFilter = warrantyFilterEl?.value || '';
                    const caseTypeFilter = caseTypeFilterEl?.value || '';
                    const companyQuery = (companyFilterEl?.value || '').trim().toLowerCase();

                    const allRows = Array.from(tbody.querySelectorAll('tr'));

                    let shown = 0;

                    allRows.forEach(tr => {

                        const item = caseReviewList[parseInt(tr.dataset.index, 10)];

                        if (!item) {
                            return;
                        }

                        const itemTower = String(getItemTower(item) || '')
                            .trim().toUpperCase();

                        const serial = String(
                            caseFullRow?.[item.caseId]?.[6] ?? ''
                        ).toLowerCase();

                        const caseIdText = String(item.caseId ?? '').toLowerCase();

                        const okTower = !tower || itemTower === tower;

                        const okSearch = !query ||
                            serial.includes(query) ||
                            caseIdText.includes(query);

                        let okLabor = true;

                        if (laborFilter) {

                            const laborValue = parseLaborCell(tr);

                            okLabor = laborFilter === 'none' ?
                                laborValue === null :
                                laborValue === Number(laborFilter);
                        }

                        const okWarranty = !warrantyFilter ||
                            String(item.warrantyStatus ?? '').trim() === warrantyFilter;

                        const okCaseType = !caseTypeFilter ||
                            String(item.caseType ?? '').trim() === caseTypeFilter;

                        const okCompany = !companyQuery ||
                            String(item.companyName ?? '').trim().toLowerCase() === companyQuery;

                        const visible = okTower && okSearch && okLabor && okWarranty && okCaseType && okCompany;

                        tr.style.display = visible ? '' : 'none';

                        if (visible) {
                            shown++;
                        }
                    });

                    const totalEl = document.getElementById('totalPartSheetText');

                    if (totalEl) {
                        totalEl.innerText = (tower || query || laborFilter || warrantyFilter || caseTypeFilter || companyQuery) ?
                            `Ditampilkan ${shown} dari ${allRows.length} case` :
                            `Total Case : ${allRows.length}`;
                    }

                    refreshTotalLabor();
                }

                if (towerFilterEl) {
                    towerFilterEl.value = '';
                    towerFilterEl.onchange = applyReviewFilter;
                }

                if (laborFilterEl) {
                    laborFilterEl.value = '';
                    rebuildLaborFilterOptions();
                    // nominal bisa berubah (ganti kategori / Dapat Labor) -> segarkan
                    // daftar opsi setiap kali dropdown dibuka
                    laborFilterEl.onmousedown = rebuildLaborFilterOptions;
                    laborFilterEl.onfocus = rebuildLaborFilterOptions;
                    laborFilterEl.onchange = applyReviewFilter;
                }

                if (companyFilterEl) {
                    reviewCompanyNames = Array.from(new Set(
                        caseReviewList
                            .map(it => String(it.companyName ?? '').trim())
                            .filter(Boolean)
                    )).sort();
                    companyFilterEl.innerHTML =
                        '<option value="">Semua Company</option>' +
                        reviewCompanyNames
                            .map(n => `<option value="${safeTextHtml(n)}">${safeTextHtml(n)}</option>`)
                            .join('');
                    companyFilterEl.value = '';
                    companyFilterEl.onchange = applyReviewFilter;
                }

                // Total Labor ikut berubah kalau kategori / nominal Labor di tabel berubah
                if (tbody._totalLaborObserver) {
                    tbody._totalLaborObserver.disconnect();
                }
                let totalLaborQueued = false;
                tbody._totalLaborObserver = new MutationObserver(() => {
                    if (totalLaborQueued) {
                        return;
                    }
                    totalLaborQueued = true;
                    requestAnimationFrame(() => {
                        totalLaborQueued = false;
                        refreshTotalLabor();
                    });
                });
                tbody._totalLaborObserver.observe(tbody, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                refreshTotalLabor();

                if (caseTypeFilterEl) {
                    // opsi diambil dari Case Type yang benar-benar ada di data
                    const caseTypeValues = Array.from(new Set(
                        caseReviewList
                            .map(it => String(it.caseType ?? '').trim())
                            .filter(Boolean)
                    )).sort();
                    caseTypeFilterEl.innerHTML =
                        '<option value="">Semua Case Type</option>' +
                        caseTypeValues.map(v => {
                            const safe = caseTypeHtml(v);
                            return `<option value="${safe}">${safe}</option>`;
                        }).join('');
                    caseTypeFilterEl.value = '';
                    caseTypeFilterEl.onchange = applyReviewFilter;
                }

                if (warrantyFilterEl) {
                    // opsi diambil dari Warranty Status yang benar-benar ada di data
                    const warrantyValues = Array.from(new Set(
                        caseReviewList
                            .map(it => String(it.warrantyStatus ?? '').trim())
                            .filter(Boolean)
                    )).sort();
                    warrantyFilterEl.innerHTML =
                        '<option value="">Semua Warranty</option>' +
                        warrantyValues.map(v => {
                            const safe = v.replace(/[&<>"']/g, ch => ({
                                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                                '"': '&quot;', "'": '&#39;'
                            }[ch]));
                            return `<option value="${safe}">${safe}</option>`;
                        }).join('');
                    warrantyFilterEl.value = '';
                    warrantyFilterEl.onchange = applyReviewFilter;
                }

                if (searchInputEl) {
                    searchInputEl.value = '';
                    searchInputEl.oninput = applyReviewFilter;
                }

                if (filterResetEl) {
                    filterResetEl.onclick = function() {
                        if (towerFilterEl) towerFilterEl.value = '';
                        if (laborFilterEl) laborFilterEl.value = '';
                        if (warrantyFilterEl) warrantyFilterEl.value = '';
                        if (caseTypeFilterEl) caseTypeFilterEl.value = '';
                        if (companyFilterEl) companyFilterEl.value = '';
                        if (searchInputEl) searchInputEl.value = '';
                        applyReviewFilter();
                    };
                }


                // ========================================================
                // TAMPILKAN MODAL
                // ========================================================

                // [DIUBAH] RR dihitung & dirender langsung di sini (client-side,
                // tanpa fetch) karena sekarang RR adalah Langkah 1.
                // [DITAMBAHKAN] MODE REVISI: isi kategori & keterangan tiap baris dari
                // data terakhir di database (bukan hasil hitung otomatis), tandai badge.
                modalEl.querySelectorAll('.pc-revision-badge').forEach(el => el.remove());

                if (revision) {

                    modalEl.querySelectorAll('.modal-title').forEach(t => {
                        const b = document.createElement('span');
                        b.className = 'badge pc-revision-badge ms-2';
                        b.style.setProperty('background', '#f59e0b', 'important');
                        b.style.setProperty('color', '#fff', 'important');
                        b.textContent = 'Mode Revisi · ' + (revision.kodeUpload || '');
                        t.appendChild(b);
                    });

                    tbody.querySelectorAll('tr').forEach(tr => {

                        const item = caseReviewList[parseInt(tr.dataset.index, 10)];

                        if (!item) {
                            return;
                        }

                        const sel = tr.querySelector('.apo-review-kategori');

                        if (
                            sel &&
                            item.revCategoryId !== undefined &&
                            item.revCategoryId !== null &&
                            item.revCategoryId !== ''
                        ) {
                            sel.value = String(item.revCategoryId);
                        }

                        const ket = tr.querySelector('.apo-review-keterangan');

                        if (ket) {
                            ket.value = item.isRR ?
                                (rrSelections[item.caseId] || '') :
                                (item.revKeterangan || '');
                        }
                    });

                    refreshAllLaborCells();
                    updateLegendCounts();
                }

                renderRrStep(clientRrGroups);

                // Selalu mulai dari Langkah 1 (Review RR)
                document.getElementById('reviewStep1').classList.remove('d-none');
                document.getElementById('reviewStep2').classList.add('d-none');

                modalEl.style.display = 'block';


