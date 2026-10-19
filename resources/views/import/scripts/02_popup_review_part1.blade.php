{{-- [PARTIAL] import.scripts.02_popup_review_part1 — bagian dari import.blade.php (baris asli 1694-2206) — showPartClassificationPopup() bagian 1/5: header fungsi, state part, helper kategori & warna baris — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
        // ============================================================
        // POPUP REVIEW PART
        //
        // FITUR:
        // 1. Semua Case ID tampil.
        // 2. Case tanpa part tetap tampil.
        // 3. Part sama memakai state kategori yang sama.
        // 4. Edit kategori pada satu part -> semua baris part tersebut ikut.
        // 5. Ganti HP Part No -> mengambil kategori part tersebut.
        // ============================================================

        function showPartClassificationPopup( caseReviewList, resolved, clientRrGroups, caseFullRow, revision = null ) {

            return new Promise((resolve, reject) => {

                const modalEl =
                    document.getElementById(
                        'partClassificationModal'
                    );

                const tbody =
                    modalEl.querySelector(
                        '#partClassificationTable tbody'
                    );

                tbody.innerHTML = '';

                // [DIPINDAH] Harus dideklarasikan di sini (sebelum dipakai),
                // karena renderRrStep() dipanggil lebih awal sekarang (pas
                // modal ditampilkan, bukan pas tombol Next diklik) -
                // kalau const ini masih di bawah, renderRrStep() akan error
                // "Cannot access before initialization" (temporal dead zone).
                const step1 = document.getElementById('reviewStep1');
                const step2 = document.getElementById('reviewStep2');

                // pilihan dropdown Keterangan RR per Case ID (tetap ada walau bolak-balik)
                const rrSelections = { ...((revision && revision.rrSelections) || {}) };


                // ====================================================
                // STATE GLOBAL PER PART
                //
                // Contoh:
                //
                // partState["654321"] = {
                //     categoryId: 5
                // }
                //
                // Jadi semua Case ID yang menggunakan part 654321
                // akan mengambil kategori yang sama.
                // ====================================================

                const partState = {};


                // ====================================================
                // INIT STATE PART DARI DATABASE
                // ====================================================

                Object.keys(resolved || {}).forEach(partNo => {

                    const info = resolved[partNo] || {};

                    let categoryId = null;


                    // Kalau backend mengirim category_id
                    if (
                        info.category_id !== undefined &&
                        info.category_id !== null &&
                        info.category_id !== ''
                    ) {

                        categoryId = info.category_id;

                    }


                    // Kalau backend hanya mengirim nama kategori
                    // cari ID dari laborCategories
                    if (
                        !categoryId &&
                        info.category
                    ) {

                        const cat =
                            laborCategories.find(
                                c =>
                                String(c.name)
                                .trim()
                                .toLowerCase() ===
                                String(info.category)
                                .trim()
                                .toLowerCase()
                            );

                        if (cat) {
                            categoryId = cat.id;
                        }
                    }


                    // Kalau belum dikenal tetapi ada guessed_category
                    if (
                        !categoryId &&
                        info.guessed_category
                    ) {

                        const cat =
                            laborCategories.find(
                                c =>
                                String(c.name)
                                .trim()
                                .toLowerCase() ===
                                String(info.guessed_category)
                                .trim()
                                .toLowerCase()
                            );

                        if (cat) {
                            categoryId = cat.id;
                        }
                    }


                    partState[partNo] = {
                        categoryId: categoryId
                    };

                });


                // ====================================================
                // FUNGSI CARI KATEGORI PART
                // ====================================================

                function getPartCategoryId(partNo) {

                    if (!partNo) {
                        return '';
                    }

                    if (
                        partState[partNo] &&
                        partState[partNo].categoryId
                    ) {

                        return partState[partNo].categoryId;
                    }


                    const info =
                        resolved[partNo] || {};


                    let categoryName =
                        info.known ?
                        info.category :
                        info.guessed_category;


                    if (categoryName) {

                        const cat =
                            laborCategories.find(
                                c =>
                                String(c.name)
                                .trim()
                                .toLowerCase() ===
                                String(categoryName)
                                .trim()
                                .toLowerCase()
                            );

                        if (cat) {

                            if (!partState[partNo]) {
                                partState[partNo] = {};
                            }

                            partState[partNo].categoryId =
                                cat.id;

                            return cat.id;
                        }
                    }

                    return '';
                }


                // ====================================================
                // SET KATEGORI PART
                // ====================================================

                function setPartCategory(
                    partNo,
                    categoryId
                ) {

                    if (!partNo) {
                        return;
                    }

                    if (!partState[partNo]) {
                        partState[partNo] = {};
                    }

                    partState[partNo].categoryId =
                        categoryId || '';
                }


                // ====================================================
                // UPDATE SEMUA BARIS YANG MEMAKAI PART YANG SAMA
                // ====================================================

                function syncPartRows(partNo) {

                    if (!partNo) {
                        return;
                    }

                    const categoryId =
                        getPartCategoryId(partNo);


                    tbody.querySelectorAll('tr').forEach(tr => {

                        const selectedPart =
                            tr.querySelector(
                                '.apo-review-partno'
                            )?.value;

                        if (
                            String(selectedPart) !==
                            String(partNo)
                        ) {
                            return;
                        }


                        const kategoriSelect =
                            tr.querySelector(
                                '.apo-review-kategori'
                            );

                        const laborTd =
                            tr.querySelector(
                                '.apo-review-labor'
                            );

                        const itemIndex =
                            parseInt(
                                tr.dataset.index,
                                10
                            );

                        const item =
                            caseReviewList[itemIndex];


                        if (!item) {
                            return;
                        }


                        const candidate =
                            item.candidates.find(
                                c =>
                                String(c.partNo) ===
                                String(partNo)
                            );


                        // --------------------------------------------
                        // Set kategori
                        // --------------------------------------------

                        if (kategoriSelect) {

                            kategoriSelect.value =
                                categoryId || '';
                        }


                        // --------------------------------------------
                        // Hitung Labor
                        // [DIUBAH] Case RR (repair ulang) selalu "-",
                        // walau part/kategorinya disamakan lewat sync ini.
                        // --------------------------------------------

                        const price =
                            isNoLaborItem(item) ?
                            null :
                            computeLaborPrice(
                                categoryId,
                                candidate?.productTower || '',
                                item.caseId
                            );

                        if (laborTd) {

                            laborTd.innerText =
                                isNoLaborItem(item) ? '-' : formatLabor(price);
                        }

                    });
                }


                // ====================================================
                // UPDATE NAMA PART
                // ====================================================

                function updatePartName(
                    tr,
                    candidate
                ) {

                    const partNameTd =
                        tr.querySelector(
                            '.apo-review-partname'
                        );

                    if (!partNameTd) {
                        return;
                    }


                    // Bersihkan isi lama
                    partNameTd.innerHTML = '';


                    const text =
                        document.createTextNode(
                            candidate?.partName || '-'
                        );

                    partNameTd.appendChild(text);


                    const badge =
                        document.createElement('span');

                    badge.className =
                        'badge bg-danger apo-review-badge-baru d-none ms-1';

                    badge.innerText =
                        'Part Baru';

                    partNameTd.appendChild(badge);
                }


                // ====================================================
                // PRODUCT TOWER & WARNA BARIS
                // [DIUBAH] Sebelumnya IPG -> hijau. Sekarang hijau dipakai
                // untuk RR (Serial No sama, Case ID beda, dalam file yang
                // sama) -> item.isRR. IPG gak lagi punya warna sendiri
                // (default putih, sama kayak PSG). Cancel tetap biru dan
                // diprioritaskan paling atas.
                // ====================================================

                function getItemTower(item) {

                    return String(
                        item.productTower ||
                        item.candidates?.[0]?.productTower ||
                        ''
                    ).trim().toUpperCase();
                }

                function getRowColorClass(item) {

                    const status =
                        String(item.caseStatus || '')
                        .trim()
                        .toLowerCase();

                    // [DIUBAH] RR (case ke-2, ke-3, dst dalam 1 Serial No di Langkah 1)
                    // diprioritaskan di atas Cancel -> tetap HIJAU walau status
                    // case-nya Cancel.
                    if (item.isRR) {
                        return 'table-success';
                    }

                    if (status.includes('cancel')) {
                        return 'row-cancel';
                    }

                    return '';
                }

                // [DITAMBAHKAN] Case status Cancel (termasuk kode 511 / SPP) tidak
                // dapat Labor, sama seperti case Cancel lainnya.
                function isCancelItem(item) {

                    return String(item.caseStatus || '')
                        .trim()
                        .toLowerCase()
                        .includes('cancel');
                }

                // [DITAMBAHKAN] Labor selalu "-" untuk RR ATAU Cancel.
                function isNoLaborItem(item) {

                    // Cancel selalu tanpa Labor.
                    if (isCancelItem(item)) {
                        return true;
                    }

                    // [DIUBAH] RR tanpa Labor, KECUALI di Langkah 1 dipilih
                    // "Dapat Labor" untuk case ini.
                    if (item.isRR) {
                        return !isRrLaborSelected(item);
                    }

                    return false;
                }

                // [DITAMBAHKAN] true kalau case RR ini dipilih "Dapat Labor"
                function isRrLaborSelected(item) {

                    if (!item || !item.isRR) {
                        return false;
                    }

                    return String(rrSelections[item.caseId] || '')
                        .trim()
                        .toLowerCase() === RR_LABOR_OPTION.toLowerCase();
                }

                // [DITAMBAHKAN] Hitung ulang sel Labor satu baris (dipakai
                // saat pilihan "Dapat Labor" di Langkah 1 berubah).
                function refreshRowLabor(tr) {

                    const item = caseReviewList[parseInt(tr.dataset.index, 10)];
                    const laborTd = tr.querySelector('.apo-review-labor');

                    if (!item || !laborTd) {
                        return;
                    }

                    if (isNoLaborItem(item)) {
                        laborTd.innerText = '-';
                        return;
                    }

                    let tower = item.productTower || '';

                    if (!item.noPart && item.candidates && item.candidates.length) {

                        const partNo = tr.querySelector('.apo-review-partno')?.value;

                        const cand = item.candidates.find(
                            c => String(c.partNo) === String(partNo)
                        );

                        tower = cand?.productTower || getItemTower(item) || tower;
                    }

                    const price = computeLaborPrice(
                        tr.querySelector('.apo-review-kategori')?.value || '',
                        tower,
                        item.caseId
                    );

                    laborTd.innerText = price != null ? formatLabor(price) : '-';
                }

                function refreshAllLaborCells() {

                    tbody.querySelectorAll('tr').forEach(refreshRowLabor);
                }


                // ====================================================
                // HIGHLIGHT PART BARU
                // ====================================================

                function updateUnknownHighlight(
                    tr,
                    partNo
                ) {

                    const isNew = !(resolved[partNo]?.known);


                    // Warna RR (hijau) / Cancel (biru) diprioritaskan di atas merah
                    // "Part Baru"; badge "Part Baru" tetap tampil.
                    tr.classList.toggle(
                        'table-danger',
                        isNew && !tr.dataset.rowColor
                    );


                    const badge =
                        tr.querySelector(
                            '.apo-review-badge-baru'
                        );


                    if (badge) {

                        badge.classList.toggle(
                            'd-none',
                            !isNew
                        );
                    }
                }


