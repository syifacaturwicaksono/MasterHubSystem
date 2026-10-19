{{-- [PARTIAL] import.scripts.06_popup_review_steps_revision — bagian dari import.blade.php (baris asli 3964-4638) — showPartClassificationPopup() bagian 5/5 (tombol close, langkah 1-2, simpan/export) + fungsi Revisi PO Labor — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                // ========================================================
                // TOMBOL CLOSE
                // ========================================================

                document.getElementById(
                    'closePartClassificationBtn'
                ).onclick = function() {

                    modalEl.style.display =
                        'none';


                    const err =
                        new Error(
                            'USER_CANCELLED'
                        );


                    err.cancelled =
                        true;


                    reject(err);
                };


                // ========================================================
                // STEP HELPERS
                // [DIUBAH] step1 = RR (Langkah 1), step2 = Part & Kategori
                // (Langkah 2), sudah dideklarasikan di atas (dekat tbody)
                // karena dibutuhkan lebih awal oleh renderRrStep(). RR
                // dihitung di awal (clientRrGroups, lihat atas) jadi gak
                // perlu fetch/tunggu server lagi.
                // ========================================================

                const nextBtn = document.getElementById('nextToRrBtn');

                function showStep(n) {

                    step1.classList.toggle('d-none', n !== 1);
                    step2.classList.toggle('d-none', n !== 2);
                }

                function escapeHtml(value) {

                    return String(value ?? '').replace(
                        /[&<>"']/g,
                        ch => ({
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#39;'
                        } [ch])
                    );
                }

                function renderRrStep(groups) {

                    // [DIUBAH] Tabel RR sekarang ada di step1, bukan step2
                    const rrTbody = step1.querySelector('#rrTable tbody');

                    // [DITAMBAHKAN] Daftar saran untuk input Keterangan RR
                    let rrDatalist = document.getElementById('rrKeteranganList');

                    if (!rrDatalist) {
                        rrDatalist = document.createElement('datalist');
                        rrDatalist.id = 'rrKeteranganList';
                        step1.appendChild(rrDatalist);
                    }

                    rrDatalist.innerHTML = RR_KETERANGAN_OPTIONS
                        .map(opt => `<option value="${escapeHtml(opt)}"></option>`)
                        .join('');

                    rrTbody.innerHTML = '';

                    step1.querySelector('#rrTableWrapper')
                        .classList.toggle('d-none', groups.length === 0);

                    step1.querySelector('#rrEmptyMsg')
                        .classList.toggle('d-none', groups.length !== 0);

                    groups.forEach((group, groupIndex) => {

                        const cases = group.cases || [];

                        const isCancelStatus = status =>
                            String(status || '').toLowerCase().includes('cancel');

                        cases.forEach((c, i) => {

                            const tr = document.createElement('tr');

                            // [DIUBAH] Seluruh baris 1 Serial No sama warnanya;
                            // antar Serial No selang-seling kuning & biru.
                            tr.classList.add(
                                groupIndex % 2 === 0
                                    ? 'rr-group-yellow'
                                    : 'rr-group-blue'
                            );

                            // [DITAMBAHKAN] Default "Dapat Labor": case pertama
                            // yang TIDAK Cancel setelah case-case sebelumnya
                            // semua Cancel. Masih bisa diganti manual.
                            if (
                                i !== 0 &&
                                rrSelections[c.case_id] === undefined &&
                                !isCancelStatus(c.case_status) &&
                                cases.slice(0, i).every(
                                    prev => isCancelStatus(prev.case_status)
                                )
                            ) {
                                rrSelections[c.case_id] = RR_LABOR_OPTION;
                            }

                            // [DIUBAH] Dropdown muncul di SEMUA case KECUALI
                            // yang pertama (case pertama = dapat Labor normal,
                            // bukan RR, jadi gak butuh keterangan RR).
                            let keteranganHtml = '';

                            if (i !== 0) {

                                // [DIUBAH] Dropdown + isi manual: input teks dengan daftar
                                // saran (datalist). User bisa pilih salah satu opsi ATAU
                                // ketik keterangan sendiri kalau opsinya tidak ada.
                                // Tombol x (clear) bawaan input search untuk menghapus.
                                keteranganHtml = `
                                <input type="search"
                                    class="form-control form-control-sm rr-keterangan"
                                    list="rrKeteranganList"
                                    autocomplete="off"
                                    placeholder="Pilih / ketik manual"
                                    value="${escapeHtml(rrSelections[c.case_id] || '')}"
                                    data-case-id="${escapeHtml(c.case_id)}">
                            `;
                            }

                            tr.innerHTML = `
                            ${i === 0
                                ? `<td rowspan="${cases.length}" class="fw-semibold align-middle">${escapeHtml(group.serial_no)}</td>`
                                : ''}
                            <td>${caseLinkHtml(c.case_id)}${c.history
                                ? ` <span class="badge bg-secondary" title="${escapeHtml(
                                    'Case bulan sebelumnya (riwayat), hanya referensi. Closed: ' + (c.closed_date || '-') +
                                    (typeof c.days === 'number' ? ' (' + c.days + ' hari sebelum case baru)' : '')
                                )}">Riwayat${typeof c.days === 'number' ? ' ' + c.days + 'd' : ''}</span>`
                                : ''}</td>
                            <td>${escapeHtml(c.company_name)}</td>
                            <td>${escapeHtml(c.product_tower)}</td>
                            <td>${escapeHtml(c.product_name)}</td>
                            <td>${escapeHtml(c.case_status)}</td>
                            <td>${warrantyStatusHtml(c.warranty_status)}</td>
                            <td>${escapeHtml(c.repair_action)}</td>
                            <td>${keteranganHtml}</td>
                        `;

                            const select = tr.querySelector('.rr-keterangan');

                            if (select) {

                                // [DITAMBAHKAN] Sinkron langsung ke input
                                // Keterangan di baris yang sama pada tabel
                                // Review Part & Kategori (Langkah 2), biar
                                // kelihatan udah keisi begitu pindah langkah.
                                syncRrKeteranganToPartRow(
                                    c.case_id,
                                    rrSelections[c.case_id] || ''
                                );

                                select.addEventListener('input', function() {

                                    const item = caseReviewList.find(
                                        it => it.caseId === this.dataset.caseId
                                    );

                                    const wasLabor = item ? isRrLaborSelected(item) : false;

                                    rrSelections[this.dataset.caseId] = this.value.trim();

                                    syncRrKeteranganToPartRow(
                                        this.dataset.caseId,
                                        this.value.trim()
                                    );

                                    // [DITAMBAHKAN] "Dapat Labor" mengubah Labor di
                                    // Langkah 2 -> hitung ulang hanya kalau statusnya berubah.
                                    if (item && wasLabor !== isRrLaborSelected(item)) {
                                        refreshAllLaborCells();
                                    }
                                });
                            }

                            rrTbody.appendChild(tr);
                        });
                    });

                    // [DITAMBAHKAN] Default "Dapat Labor" baru terisi di atas,
                    // setelah baris Langkah 2 selesai dirender -> sinkronkan Labor.
                    refreshAllLaborCells();
                }

                // [DITAMBAHKAN] Set value input Keterangan di tabel Part &
                // Kategori (Langkah 2) berdasarkan pilihan dropdown RR di
                // Langkah 1, supaya begitu Next diklik keterangannya udah
                // langsung kelihatan terisi (tetap bisa diedit manual lagi).
                function syncRrKeteranganToPartRow(caseId, keterangan) {

                    const idx =
                        caseReviewList.findIndex(
                            it => it.caseId === caseId
                        );

                    if (idx === -1) {
                        return;
                    }

                    const partRow =
                        tbody.querySelector(
                            `tr[data-index="${idx}"] .apo-review-keterangan`
                        );

                    if (partRow) {
                        partRow.value = keterangan;
                    }
                }

                // ========================================================
                // [DIUBAH] TOMBOL NEXT (Langkah 1 RR -> Langkah 2 Part & Kategori)
                // Cuma tukar tampilan, gak ada fetch/network sama sekali -
                // RR-nya udah dirender duluan waktu modal dibuka.
                // ========================================================

                nextBtn.onclick = function() {
                    refreshAllLaborCells();
                    showStep(2);
                };


                // ========================================================
                // LANGKAH 2: KEMBALI (balik ke Review RR, pilihan masih utuh)
                // ========================================================

                document.getElementById('rrBackBtn').onclick = function() {
                    showStep(1);
                };


                // ========================================================
                // [DIUBAH] Isi hidden field override dari hasil Langkah 2
                // (Part & Kategori), DIGABUNG dengan pilihan dropdown
                // Keterangan dari Langkah 1 (RR) -> rrSelections. Kalau
                // case yang sama diisi manual juga di Langkah 2, yang di
                // Langkah 2 menang (lebih spesifik/terakhir direview).
                // ========================================================

                function fillOverrideHiddenFields(data) {

                    document.getElementById('apoCaseOverrides').value =
                        JSON.stringify(data.caseOverrides || {});

                    const laborOverrides = {};
                    const keteranganOverrides = {
                        ...rrSelections
                    };

                    Object.keys(data.winners || {}).forEach(caseId => {

                        const winner = data.winners[caseId];

                        if (
                            winner &&
                            winner.price !== null &&
                            winner.price !== undefined &&
                            winner.price !== ''
                        ) {
                            laborOverrides[caseId] = Number(winner.price);
                        }

                        if (winner && winner.keterangan) {
                            keteranganOverrides[caseId] = winner.keterangan;
                        }
                    });

                    document.getElementById('apoLaborOverrides').value =
                        JSON.stringify(laborOverrides);

                    document.getElementById('apoKeteranganOverrides').value =
                        JSON.stringify(keteranganOverrides);
                }


                // ========================================================
                // LANGKAH 2: SIMPAN -> langsung submit form ke /import
                // (gak ada lagi tahap stash/confirm terpisah, karena file
                // belum pernah dikirim ke server sebelum titik ini).
                // ========================================================

                // [DITAMBAHKAN] Simpan revisi ke database (baris apo yang ada diperbarui)
                async function submitRevision(data) {

                    const overlay = document.getElementById('loadingOverlay');
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    if (overlay) {
                        overlay.style.display = 'flex';
                    }

                    try {

                        if (data.classifications && data.classifications.length > 0) {

                            const respClass = await fetch('{{ route("upload.savePartClassification") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    classifications: data.classifications
                                })
                            });

                            if (!respClass.ok) {
                                throw new Error('Gagal menyimpan klasifikasi part.');
                            }
                        }

                        const rows = [];

                        caseReviewList.forEach(item => {

                            const w = (data.winners || {})[item.caseId];

                            if (!w) {
                                return;
                            }

                            rows.push({
                                case_id: item.caseId,
                                part_no: w.partNo || null,
                                part_name: w.partName || null,
                                category: w.category || null,
                                labor: (w.price === null || w.price === undefined || w.price === '') ?
                                    null : Number(w.price),
                                keterangan: w.keterangan || rrSelections[item.caseId] || '',
                                is_rr: !!item.isRR
                            });
                        });

                        const resp = await fetch('{{ route("upload.revisiSave") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({
                                kode_upload: revision.kodeUpload,
                                rows: rows
                            })
                        });

                        const json = await resp.json().catch(() => ({}));

                        if (!resp.ok) {
                            throw new Error(json.message || 'Gagal menyimpan revisi.');
                        }

                        modalEl.style.display = 'none';

                        alert(json.message || 'Revisi berhasil disimpan.');

                        window.location.reload();

                    } catch (e) {

                        if (overlay) {
                            overlay.style.display = 'none';
                        }

                        alert((e && e.message) || 'Gagal menyimpan revisi.');
                    }
                }

                document.getElementById('savePartClassificationBtn').onclick = function() {

                    const data = collectReviewData();

                    if (revision) {
                        submitRevision(data);
                        return;
                    }


                    fillOverrideHiddenFields(data);

                    modalEl.style.display = 'none';

                    const overlay = document.getElementById('loadingOverlay');

                    if (overlay) {
                        overlay.style.display = 'flex';
                    }

                    document.getElementById('uploadForm').submit();
                };


                // ========================================================
                // LANGKAH 2: EXPORT -> unduh review-nya doang, TIDAK submit
                // ke server / commit ke DB.
                // ========================================================

                document.getElementById('rrExportBtn').onclick = function() {

                    const data = collectReviewData();

                    exportReviewAsCsv(data.exportRows);
                };


                // ========================================================
                // LANGKAH 2: SIMPAN & EXPORT -> export dulu, baru submit.
                // ========================================================

                document.getElementById('rrSaveExportBtn').onclick = function() {

                    const data = collectReviewData();

                    exportReviewAsCsv(data.exportRows);

                    if (revision) {
                        submitRevision(data);
                        return;
                    }


                    fillOverrideHiddenFields(data);

                    modalEl.style.display = 'none';

                    const overlay = document.getElementById('loadingOverlay');

                    if (overlay) {
                        overlay.style.display = 'flex';
                    }

                    document.getElementById('uploadForm').submit();
                };

            });
        }


        
        // ============================================================
        // [DITAMBAHKAN] REVISI PO LABOR
        // Ambil data PO Labor terakhir dari database (belum terkirim),
        // susun ulang jadi data popup yang sama seperti hasil upload
        // (RR -> Next -> Review Part), lalu buka popup dalam mode revisi.
        // ============================================================

        function revisiManualKeterangan(r) {

            let k = String(r.keterangan ?? '').trim();
            const ra = String(r.repair_action ?? '').trim();

            // Kosong / sama dengan Repair Action = hasil fallback, bukan isian user
            if (!k || k === ra) {
                return '';
            }

            k = k.replace(/^RR\s*-\s*/i, '');

            if (/^RR$/i.test(k)) {
                k = '';
            }

            k = k.replace(/^Close Repair tanpa part\s*(-\s*)?/i, '');
            k = k.replace(/\bVoid Case\b\s*(-\s*)?/i, '');

            return k.replace(/^[\s\-]+|[\s\-]+$/g, '').trim();
        }

        async function startPoLaborRevision() {

            const btn = document.getElementById('revisiPoLaborBtn');
            const overlay = document.getElementById('loadingOverlay');
            const norm = v => String(v ?? '').trim();

            try {

                if (btn) {
                    btn.disabled = true;
                }

                if (overlay) {
                    overlay.style.display = 'flex';
                }

                const res = await fetch('{{ route("upload.revisiData") }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(json.message || 'Gagal mengambil data revisi.');
                }

                const rows = json.rows || [];

                if (rows.length === 0) {
                    throw new Error('Tidak ada data yang bisa direvisi (semua sudah terkirim).');
                }

                const catByName = {};

                laborCategories.forEach(c => {
                    catByName[norm(c.name).toLowerCase()] = c;
                });

                const caseFullRow = {};
                const resolved = {};
                const rrSelections = {};

                const caseReviewList = rows.map(r => {

                    const caseId = norm(r.case_id);
                    const tower = norm(r.product_tower).toUpperCase();
                    const partNo = norm(r.part_no);
                    const cat = catByName[norm(r.part_category).toLowerCase()] || null;
                    const statusLower = norm(r.case_status).toLowerCase();

                    // Baris mentah dengan urutan kolom yang sama seperti sheet
                    // Transaction Report (dipakai tabel & Excel export popup)
                    const raw = [];
                    raw[1] = caseId;
                    raw[2] = r.case_id_manual ?? '';
                    raw[3] = r.company_name ?? '';
                    raw[4] = r.received_date ? String(r.received_date).substring(0, 10) : '';
                    raw[5] = r.warranty_status ?? '';
                    raw[6] = r.serial_no ?? '';
                    raw[7] = r.product_tower ?? '';
                    raw[8] = r.product_no ?? '';
                    raw[9] = r.product_name ?? '';
                    raw[10] = r.case_type ?? '';
                    raw[11] = r.case_status ?? '';
                    raw[12] = r.problem_desc ?? '';
                    raw[13] = r.repair_action ?? '';
                    raw[14] = r.customer_company ?? '';
                    raw[15] = r.customer_name ?? '';
                    raw[16] = r.pic_name ?? '';
                    raw[17] = r.city ?? '';
                    raw[18] = r.phone_no ?? '';
                    raw[19] = r.mobile_no ?? '';
                    raw[20] = r.start_repair_date ? String(r.start_repair_date).substring(0, 10) : '';
                    raw[21] = r.tat_case ?? '';
                    raw[22] = r.finish_date ? String(r.finish_date).substring(0, 10) : '';
                    raw[23] = r.closed_date ? String(r.closed_date).substring(0, 10) : '';
                    caseFullRow[caseId] = raw;

                    const manual = revisiManualKeterangan(r);

                    if (r.is_rr) {
                        rrSelections[caseId] = manual;
                    }

                    const base = {
                        caseId: caseId,
                        productTower: tower,
                        caseStatus: r.case_status ?? '',
                        warrantyStatus: r.warranty_status ?? '',
                        caseType: r.case_type ?? '',
                        companyName: r.company_name ?? '',
                        repairAction: r.repair_action ?? '',
                        isRR: !!r.is_rr,
                        // data terakhir di database -> dipakai mengisi baris
                        revCategoryId: cat ? cat.id : '',
                        revKeterangan: manual
                    };

                    if (!partNo) {

                        return {
                            ...base,
                            candidates: [],
                            selectedPartNo: '',
                            noPart: true,
                            categoryId: cat ? cat.id : '',
                            categoryName: cat ? cat.name : '',
                            labor: (r.labor === null || r.labor === undefined) ? null : Number(r.labor),
                            autoRebuild: !!cat &&
                                norm(cat.name).toLowerCase() === 'rebuild, re install, recovery' &&
                                statusLower.includes('close repair')
                        };
                    }

                    resolved[partNo] = {
                        known: true,
                        category: r.part_category ?? '',
                        price: (r.labor === null || r.labor === undefined) ? null : Number(r.labor),
                        guessed_category: null
                    };

                    return {
                        ...base,
                        candidates: [{
                            partNo: partNo,
                            partName: r.part_name ?? '',
                            productTower: tower,
                            status: 'defective',
                            partRequestDate: '',
                            rowIndex: 0
                        }],
                        selectedPartNo: partNo,
                        noPart: false
                    };
                });

                const clientRrGroups = (json.rr_groups || []).map(g => ({
                    serial_no: g.serial_no,
                    cases: g.cases
                }));

                const kodeInput = document.getElementById('kodeUpload');

                if (kodeInput && !kodeInput.value) {
                    kodeInput.value = json.kode_upload || '';
                }

                if (overlay) {
                    overlay.style.display = 'none';
                }

                try {

                    await showPartClassificationPopup(
                        caseReviewList,
                        resolved,
                        clientRrGroups,
                        caseFullRow, {
                            kodeUpload: json.kode_upload,
                            rrSelections: rrSelections
                        }
                    );

                } catch (err) {

                    // User menutup popup = batal, bukan error
                    if (!(err && err.cancelled)) {
                        throw err;
                    }
                }

            } catch (e) {

                alert((e && e.message) || 'Gagal membuka revisi.');

            } finally {

                if (btn) {
                    btn.disabled = false;
                }

                if (overlay) {
                    overlay.style.display = 'none';
                }
            }
        }

