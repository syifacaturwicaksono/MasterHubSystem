{{-- [PARTIAL] import.scripts.03_popup_review_render_cases — bagian dari import.blade.php (baris asli 2207-2842) — showPartClassificationPopup() bagian 2/5: render semua case ke tabel — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                // ====================================================
                // RENDER SEMUA CASE
                // ====================================================

                caseReviewList.forEach(
                    (item, index) => {

                        const tr =
                            document.createElement('tr');

                        tr.dataset.index = index;

                        // warna baris: Cancel = biru, RR = hijau
                        const rowColorClass = getRowColorClass(item);

                        if (rowColorClass) {
                            tr.dataset.rowColor = rowColorClass;
                            tr.classList.add(rowColorClass);
                        }

                        // IPG = biru (kalah prioritas dari Cancel/RR/Merah/Kuning)
                        if (getItemTower(item) === 'IPG') {
                            tr.classList.add('row-ipg');
                        }


                        // =================================================
                        // CASE TANPA PART
                        // =================================================

                        if (
                            item.noPart ||
                            !item.candidates ||
                            item.candidates.length === 0
                        ) {

                            // [DITAMBAHKAN] Dropdown kategori juga dipasang di
                            // case tanpa part (sebelumnya cuma teks statis
                            // "Tidak ada part" / "-", jadi kategori & labor
                            // yang sudah dihitung di atas gak pernah kepakai).
                            const noPartCategoryOptions =
                                laborCategories.map(cat => {

                                    const selected =
                                        String(cat.id) ===
                                        String(item.categoryId) ?
                                        'selected' :
                                        '';

                                    return `
                                    <option value="${cat.id}" ${selected}>
                                        ${cat.name}
                                    </option>
                                `;

                                }).join('');

                            tr.innerHTML = `
                            <td class="fw-semibold">
                                ${safeTextHtml(item.companyName)}
                            </td>
                            <td>
                            ${caseLinkHtml(item.caseId)}
                            ${caseSerialSmallHtml(caseFullRow?.[item.caseId]?.[6])}
                        </td>

                            <td>
                                ${item.caseStatus || '-'}
                            </td>

                            <td>
                                ${warrantyStatusHtml(item.warrantyStatus)}
                            </td>

                            <td class="fw-semibold">
                                ${getItemTower(item) || '-'}
                            </td>

                            <td>
                                ${caseTypeHtml(item.caseType)}
                            </td>

                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    0
                                </span>
                            </td>

                            <td colspan="2"
                                class="text-center text-muted fw-semibold">

                                Tidak ada part

                            </td>

                            <td>
                                <div class="apo-review-repair-box">${safeTextHtml(item.repairAction)}</div>
                            </td>

                            <td>
                                <select
                                    class="form-select form-select-sm apo-review-kategori">
                                    <option value="">-- pilih --</option>
                                    ${noPartCategoryOptions}
                                </select>
                            </td>

                            <td class="text-end apo-review-labor">
                                ${isNoLaborItem(item) ? '-' : (item.labor != null ? formatLabor(item.labor) : '-')}
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm apo-review-keterangan"
                                    placeholder="Keterangan manual (opsional)">
                            </td>
                        `;

                            // [DITAMBAHKAN] blok warna penanda kalau kategori
                            // ini keisi otomatis dari rule "close repair tanpa part"
                            tr.classList.toggle(
                                'table-warning',
                                !!item.autoRebuild && !tr.dataset.rowColor
                            );

                            // [DITAMBAHKAN] recompute Labor kalau user ganti
                            // kategori manual di dropdown ini
                            // [DIUBAH] Case RR (repair ulang) selalu Labor "-"
                            // walau kategorinya diganti-ganti.
                            const noPartKategoriSelect =
                                tr.querySelector('.apo-review-kategori');

                            const noPartLaborTd =
                                tr.querySelector('.apo-review-labor');

                            noPartKategoriSelect.addEventListener(
                                'change',
                                () => {

                                    if (isNoLaborItem(item)) {
                                        noPartLaborTd.innerText = '-';
                                        return;
                                    }

                                    const price =
                                        computeLaborPrice(
                                            noPartKategoriSelect.value,
                                            item.productTower || '',
                                            item.caseId
                                        );

                                    noPartLaborTd.innerText =
                                        price != null ? formatLabor(price) : '-';

                                }
                            );

                            tbody.appendChild(tr);

                            return;
                        }


                        // =================================================
                        // HILANGKAN DUPLIKAT PART DALAM SATU CASE
                        // =================================================

                        const uniqueCandidates = [];

                        const seenPartNo = new Set();


                        item.candidates.forEach(c => {

                            const key =
                                String(c.partNo);

                            if (
                                !seenPartNo.has(key)
                            ) {

                                seenPartNo.add(key);

                                uniqueCandidates.push(c);
                            }
                        });


                        item.candidates =
                            uniqueCandidates;


                        // =================================================
                        // OPTIONS PART
                        // =================================================

                        const partOptions =
                            item.candidates.map(c => {

                                const selected =
                                    String(c.partNo) ===
                                    String(item.selectedPartNo) ?
                                    'selected' :
                                    '';


                                const isNew = !(resolved[c.partNo]?.known);


                                const label =
                                    `${isNew ? '🆕 ' : ''}` +
                                    `${c.partNo} — ` +
                                    `${c.partName || '(tanpa nama)'}` +
                                    ` [${c.status || '-'}]`;


                                return `
                                <option
                                    value="${c.partNo}"
                                    ${selected}>
                                    ${label}
                                </option>
                            `;

                            }).join('');


                        // =================================================
                        // PART TERPILIH
                        // =================================================

                        const selectedPartNo =
                            item.selectedPartNo ||
                            item.candidates[0]?.partNo ||
                            '';


                        const selectedCandidate =
                            item.candidates.find(
                                c =>
                                String(c.partNo) ===
                                String(selectedPartNo)
                            ) ||
                            item.candidates[0];


                        // =================================================
                        // KATEGORI
                        // =================================================

                        const categoryId =
                            getPartCategoryId(
                                selectedPartNo
                            );


                        const categoryOptions =
                            laborCategories.map(cat => {

                                const selected =
                                    String(cat.id) ===
                                    String(categoryId) ?
                                    'selected' :
                                    '';


                                return `
                                <option
                                    value="${cat.id}"
                                    ${selected}>
                                    ${cat.name}
                                </option>
                            `;

                            }).join('');


                        // =================================================
                        // HTML ROW
                        // =================================================

                        tr.innerHTML = `

                        <td class="fw-semibold">
                            ${safeTextHtml(item.companyName)}
                        </td>

                        <td>
                            ${caseLinkHtml(item.caseId)}
                            ${caseSerialSmallHtml(caseFullRow?.[item.caseId]?.[6])}
                        </td>

                        <td>
                            ${item.caseStatus || '-'}
                        </td>

                        <td>
                            ${warrantyStatusHtml(item.warrantyStatus)}
                        </td>

                        <td class="fw-semibold">
                            ${getItemTower(item) || '-'}
                        </td>

                        <td>
                            ${caseTypeHtml(item.caseType)}
                        </td>

                        <td class="text-center">
                            ${item.candidates.length}
                        </td>

                        <td>

                            <select
                                class="form-select form-select-sm apo-review-partno">

                                ${partOptions}

                            </select>

                        </td>

                        <td class="apo-review-partname">

                            ${selectedCandidate?.partName || '-'}

                            <span
                                class="badge bg-danger apo-review-badge-baru d-none ms-1">
                                Part Baru
                            </span>

                        </td>

                        <td>
                            <div class="apo-review-repair-box">${safeTextHtml(item.repairAction)}</div>
                        </td>

                        <td>

                            <select
                                class="form-select form-select-sm apo-review-kategori">

                                ${categoryOptions}

                            </select>

                        </td>

                        <td class="text-end apo-review-labor">
                            -
                        </td>

                        <td>
                            <input type="text"
                                class="form-control form-control-sm apo-review-keterangan"
                                placeholder="Keterangan manual (opsional)">
                        </td>

                    `;


                        tbody.appendChild(tr);


                        // =================================================
                        // ELEMENT
                        // =================================================

                        const partSelect =
                            tr.querySelector(
                                '.apo-review-partno'
                            );

                        const kategoriSelect =
                            tr.querySelector(
                                '.apo-review-kategori'
                            );

                        const laborTd =
                            tr.querySelector(
                                '.apo-review-labor'
                            );


                        // =================================================
                        // INIT KATEGORI KE STATE PART
                        // =================================================

                        if (
                            selectedPartNo &&
                            categoryId
                        ) {

                            setPartCategory(
                                selectedPartNo,
                                categoryId
                            );
                        }


                        // =================================================
                        // INIT HIGHLIGHT
                        // =================================================

                        updateUnknownHighlight(
                            tr,
                            selectedPartNo
                        );


                        // =================================================
                        // INIT LABOR
                        // [DIUBAH] Case RR (repair ulang, bukan case pertama)
                        // selalu Labor "-", gak peduli kategorinya apa.
                        // =================================================

                        const initialPrice =
                            isNoLaborItem(item) ?
                            null :
                            computeLaborPrice(
                                categoryId,
                                selectedCandidate?.productTower || '',
                                item.caseId
                            );


                        laborTd.innerText =
                            isNoLaborItem(item) ? '-' : formatLabor(initialPrice);


                        // =================================================
                        // EVENT: GANTI PART
                        // =================================================

                        partSelect.addEventListener(
                            'change',
                            function() {

                                const oldPartNo =
                                    tr.dataset.currentPartNo ||
                                    selectedPartNo;


                                const newPartNo =
                                    this.value;


                                const candidate =
                                    item.candidates.find(
                                        c =>
                                        String(c.partNo) ===
                                        String(newPartNo)
                                    );


                                // -----------------------------------------
                                // Simpan part yang sekarang
                                // -----------------------------------------

                                tr.dataset.currentPartNo =
                                    newPartNo;


                                // -----------------------------------------
                                // Update nama part
                                // -----------------------------------------

                                updatePartName(
                                    tr,
                                    candidate
                                );


                                // -----------------------------------------
                                // Highlight
                                // -----------------------------------------

                                updateUnknownHighlight(
                                    tr,
                                    newPartNo
                                );

                                // [DITAMBAHKAN] status "Part Baru" bisa berubah
                                // saat ganti part -> hitung ulang total warna
                                updateLegendCounts();


                                // -----------------------------------------
                                // Ambil kategori dari STATE PART
                                //
                                // Kalau part pernah diedit sebelumnya,
                                // gunakan kategori yang sudah diedit.
                                // -----------------------------------------

                                let newCategoryId =
                                    getPartCategoryId(
                                        newPartNo
                                    );


                                // -----------------------------------------
                                // Kalau belum ada kategori,
                                // ambil suggestion dari database.
                                // -----------------------------------------

                                if (!newCategoryId) {

                                    const info =
                                        resolved[newPartNo] || {};

                                    const categoryName =
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

                                            newCategoryId =
                                                cat.id;

                                            setPartCategory(
                                                newPartNo,
                                                newCategoryId
                                            );
                                        }
                                    }
                                }


                                // -----------------------------------------
                                // Set dropdown kategori
                                // -----------------------------------------

                                kategoriSelect.value =
                                    newCategoryId || '';


                                // -----------------------------------------
                                // Hitung Labor
                                // [DIUBAH] Case RR (repair ulang) selalu "-"
                                // -----------------------------------------

                                const price =
                                    isNoLaborItem(item) ?
                                    null :
                                    computeLaborPrice(
                                        newCategoryId,
                                        candidate?.productTower || '',
                                        item.caseId
                                    );


                                laborTd.innerText =
                                    isNoLaborItem(item) ? '-' : formatLabor(price);


                                // -----------------------------------------
                                // PENTING:
                                // Sinkronkan semua Case ID yang memakai
                                // part yang sama.
                                // -----------------------------------------

                                syncPartRows(
                                    newPartNo
                                );

                            }
                        );


                        // =================================================
                        // EVENT: GANTI KATEGORI
                        // =================================================

                        kategoriSelect.addEventListener(
                            'change',
                            function() {

                                const chosenPartNo =
                                    partSelect.value;


                                if (!chosenPartNo) {
                                    return;
                                }


                                // -----------------------------------------
                                // SIMPAN KATEGORI KE STATE GLOBAL PART
                                // -----------------------------------------

                                setPartCategory(
                                    chosenPartNo,
                                    this.value
                                );


                                // -----------------------------------------
                                // Sinkronkan SEMUA baris dengan part sama
                                // -----------------------------------------

                                syncPartRows(
                                    chosenPartNo
                                );

                            }
                        );


                        // =================================================
                        // SET PART SAAT AWAL
                        // =================================================

                        tr.dataset.currentPartNo =
                            selectedPartNo;

                    }
                );


