{{-- [PARTIAL] import.scripts.05_popup_review_collect_export — bagian dari import.blade.php (baris asli 3326-3963) — showPartClassificationPopup() bagian 4/5: collect data & export XLSX/CSV — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                // ========================================================
                // COLLECT DATA
                // ========================================================

                function collectReviewData() {

                    const winners = {};

                    const classifications = [];

                    const caseOverrides = {};

                    const exportRows = [];


                    // --------------------------------------------
                    // DEDUP CLASSIFICATION
                    //
                    // Part yang sama hanya dikirim satu kali.
                    // --------------------------------------------

                    const classificationMap = {};

                    // [DIUBAH] Kolom "Status" di Excel export dihapus. Semua isi
                    // Keterangan masuk ke kolom Keterangan: pilihan dropdown RR
                    // (Dapat Labor, Different Issue, dst) + isian manual di Review
                    // Part (keduanya sudah ada di input Keterangan baris itu), dan
                    // case berstatus Cancel diberi awalan "Cancel".
                    // [DIUBAH] Kata "Dapat Labor" TIDAK ikut ke Excel export: case
                    // tetap dapat Labor & kategori (di web & kolom Labor), tapi
                    // keterangan Excel-nya dikosongkan dari kata itu. Kalau ada
                    // teks lain (mis. "Dapat Labor - Different Issue"), teks
                    // lainnya tetap dipakai.
                    const buildExportKeterangan = (item, manual) => {

                        const text = String(manual || '')
                            .replace(/dapat labor/gi, '')
                            .replace(/^[\s\-–,;:]+|[\s\-–,;:]+$/g, '')
                            .replace(/\s{2,}/g, ' ')
                            .trim();

                        if (isCancelItem(item) && !/cancel/i.test(text)) {
                            return text ? 'Cancel - ' + text : 'Cancel';
                        }

                        return text;
                    };

                    // [DITAMBAHKAN] Void Case dikirim ke server lewat Keterangan
                    // (ApoImport mengenali kata "Void Case" -> Labor kosong).
                    const withVoidLabel = (isVoid, manual) => {

                        if (!isVoid) {
                            return manual;
                        }

                        if (/void case/i.test(manual)) {
                            return manual;
                        }

                        return manual ? 'Void Case - ' + manual : 'Void Case';
                    };

                    // [DITAMBAHKAN] Onsite / CISS / SARC dikirim ke server lewat
                    // penanda tersembunyi "[[CAT:Nama]]" di awal Keterangan.
                    // ApoImport membuang penanda ini sebelum Keterangan disimpan,
                    // jadi tidak muncul di tabel / BC / Excel.
                    const withNoLaborMarker = (categoryId, manual) => {

                        const name = noLaborCategoryName(categoryId);

                        if (!name) {
                            return manual;
                        }

                        return manual ?
                            `[[CAT:${name}]] ${manual}` :
                            `[[CAT:${name}]]`;
                    };


                    tbody.querySelectorAll('tr')
                        .forEach(tr => {

                            const index =
                                parseInt(
                                    tr.dataset.index,
                                    10
                                );


                            const item =
                                caseReviewList[index];


                            if (!item) {
                                return;
                            }


                            // ========================================
                            // CASE TANPA PART
                            // ========================================

                            if (
                                item.noPart ||
                                !item.candidates ||
                                item.candidates.length === 0
                            ) {

                                const productTower = String(item.productTower ?? '').trim().toUpperCase();

                                const kategoriSelect = tr.querySelector('.apo-review-kategori');
                                const selectedCategoryId = kategoriSelect?.value || item.categoryId || '';

                                const categoryRow = laborCategories.find(cat =>
                                    String(cat.id) === String(selectedCategoryId)
                                );

                                // [DIPERBAIKI] Sebelumnya hardcode: cuma dikasih harga kalau
                                // kategori terpilih persis "Other Part" dan tower PSG (50000),
                                // di luar itu selalu null — walau user pilih kategori lain
                                // (mis. "Rebuild, Re install, Recovery") yang sudah punya
                                // psg_price/ipg_price di labor_categories. Sekarang konsisten
                                // pakai computeLaborPrice() seperti bagian lain di file ini.
                                // [DIUBAH] Case RR (repair ulang) selalu null, gak dapat Labor.
                                const price =
                                    isNoLaborItem(item) ?
                                    null :
                                    (selectedCategoryId ?
                                        computeLaborPrice(selectedCategoryId, productTower, item.caseId) :
                                        null);

                                const keteranganInput = tr.querySelector('.apo-review-keterangan');
                                const keteranganManual = keteranganInput?.value?.trim() || '';

                                const isVoid = isVoidCategoryId(selectedCategoryId);
                                const keterangan = withNoLaborMarker(
                                    selectedCategoryId,
                                    withVoidLabel(isVoid, keteranganManual)
                                );

                                winners[item.caseId] = {
                                    partNo: null,
                                    partName: null,
                                    category: categoryRow?.name ?? null,
                                    price: price,
                                    keterangan: keterangan
                                };

                                item.categoryId = selectedCategoryId;
                                item.categoryName = categoryRow?.name ?? '';
                                item.labor = price;

                                exportRows.push({
                                    caseId: item.caseId,
                                    caseStatus: item.caseStatus || '',
                                    totalPart: 0,
                                    partNo: '',
                                    partName: 'Tidak ada part',
                                    repairAction: item.repairAction || '',
                                    kategori: categoryRow?.name ?? '',
                                    labor: price ?? '',
                                    keterangan: buildExportKeterangan(item, keteranganManual)
                                });

                                return;
                            }


                            // ========================================
                            // PART TERPILIH
                            // ========================================

                            const partSelect =
                                tr.querySelector(
                                    '.apo-review-partno'
                                );


                            const kategoriSelect =
                                tr.querySelector(
                                    '.apo-review-kategori'
                                );


                            const partNo =
                                partSelect?.value || '';


                            const categoryId =
                                kategoriSelect?.value || '';


                            const candidate =
                                item.candidates.find(
                                    c =>
                                    String(c.partNo) ===
                                    String(partNo)
                                );


                            const partName =
                                candidate?.partName || '';


                            const categoryRow =
                                laborCategories.find(
                                    c =>
                                    String(c.id) ===
                                    String(categoryId)
                                );


                            // ========================================
                            // LABOR
                            // [DIUBAH] Case RR (repair ulang) selalu null,
                            // gak dapat Labor walau punya part.
                            // ========================================

                            const price =
                                isNoLaborItem(item) ?
                                null :
                                computeLaborPrice(
                                    categoryId,
                                    candidate?.productTower || '',
                                    item.caseId
                                );


                            // ========================================
                            // WINNER PER CASE
                            // ========================================

                            const keteranganInput =
                                tr.querySelector('.apo-review-keterangan');

                            const keteranganManual =
                                keteranganInput?.value?.trim() || '';

                            const isVoid = isVoidCategoryId(categoryId);

                            const keterangan = withNoLaborMarker(
                                categoryId,
                                withVoidLabel(isVoid, keteranganManual)
                            );

                            winners[item.caseId] = {

                                partNo: partNo,

                                partName: partName,

                                category: categoryRow?.name ?? null,

                                price: price,

                                keterangan: keterangan

                            };


                            // ========================================
                            // CASE OVERRIDE
                            // ========================================

                            if (partNo) {

                                caseOverrides[item.caseId] =
                                    partNo;
                            }


                            // ========================================
                            // CLASSIFICATION PER PART
                            //
                            // Kalau part yang sama muncul 10 case,
                            // tetap hanya satu classification.
                            // ========================================

                            // [DIUBAH] Void Case TIDAK disinkronkan ke master part
                            // (kategori virtual, bukan kategori part sebenarnya).
                            if (partNo && !isVoid && !noLaborCategoryName(categoryId)) {

                                classificationMap[partNo] = {

                                    part_no: partNo,

                                    part_name: partName,

                                    category_id: categoryId,

                                    // [DITAMBAHKAN] dibutuhkan server buat
                                    // hitung harga & sync ke sparepart_master
                                    product_tower: candidate?.productTower || ''

                                };

                            }


                            // ========================================
                            // EXPORT
                            // ========================================

                            exportRows.push({

                                caseId: item.caseId,

                                caseStatus: item.caseStatus || '',

                                totalPart: item.candidates.length,

                                partNo: partNo,

                                partName: partName,

                                repairAction: item.repairAction || '',

                                kategori: categoryRow?.name ?? '',

                                labor: price ?? '',

                                keterangan: buildExportKeterangan(item, keteranganManual)

                            });

                        });


                    // ================================================
                    // CONVERT CLASSIFICATION MAP
                    // ================================================

                    Object.values(
                        classificationMap
                    ).forEach(row => {

                        classifications.push(row);

                    });


                    return {

                        winners,

                        classifications,

                        caseOverrides,

                        exportRows

                    };
                }


                // ========================================================
                // EXPORT XLSX
                // ========================================================

                // [DIUBAH] Format kolom disamakan dengan contoh "ISD Labor
                // Report" (PO_Labor_KLA_Computer...xlsx): judul 2 baris,
                // baris kosong, lalu header lengkap dari sheet Transaction
                // Report (Case ID Manual, Company Name, Received Date, dst)
                // + kolom hasil review (HP Part No, Part Name, Kategori,
                // Keterangan) di akhir. Filter tetap aktif di baris header.
                function formatExcelDateOnly(value) {

                    if (value === null || value === undefined || value === '') {
                        return '';
                    }

                    if (typeof value === 'number') {

                        const d = XLSX.SSF.parse_date_code(value);

                        if (!d) {
                            return '';
                        }

                        return String(d.d).padStart(2, '0') + '/' +
                            String(d.m).padStart(2, '0') + '/' +
                            d.y;
                    }

                    return String(value);
                }

                function exportReviewAsCsv(
                    exportRows
                ) {

                    const header = [
                        'No',
                        'Case ID',
                        'Case ID Manual',
                        'Company Name',
                        'Received Date',
                        'Warranty Status',
                        'Serial No.',
                        'Product Tower',
                        'Product No.',
                        'Product Name',
                        'Case Type',
                        'Case Status',
                        'Problem Desc.',
                        'Repair Action',
                        'Customer Company',
                        'Customer Name',
                        'PIC Name',
                        'City',
                        'Phone No.',
                        'Mobile No.',
                        'Start Repair Date',
                        'TAT Case (received-finish)',
                        'Finish Date',
                        'Closed Date',
                        'Labor',
                        'Keterangan'
                    ];


                    // Judul laporan (2 baris) + 1 baris kosong sebelum header,
                    // sama seperti contoh
                    const kodeUploadValue =
                        document.getElementById('kodeUpload')?.value || '';

                    const aoa = [
                        ['ISD Labor Report'],
                        ['Batch : ' + kodeUploadValue],
                        [],
                        header
                    ];

                    const headerRowIndex = aoa.length - 1;

                // =====================================================
                // URUTKAN CASE ID DARI TERKECIL KE TERBESAR
                // =====================================================
                    exportRows.sort((a, b) => {
                        const caseA = Number(a.caseId) || 0;
                        const caseB = Number(b.caseId) || 0;
                    
                        return caseA - caseB;
                    });

                    exportRows.forEach((row, idx) => {

                        const raw =
                            caseFullRow[row.caseId] || [];

                        aoa.push([

                            idx + 1,

                            row.caseId,

                            raw[2] ?? '', // Case ID Manual

                            raw[3] ?? '', // Company Name

                            formatExcelDateOnly(raw[4]), // Received Date

                            raw[5] ?? '', // Warranty Status

                            raw[6] ?? '', // Serial No.

                            raw[7] ?? '', // Product Tower

                            raw[8] ?? '', // Product No.

                            raw[9] ?? '', // Product Name

                            raw[10] ?? '', // Case Type

                            row.caseStatus || raw[11] || '',

                            raw[12] ?? '', // Problem Desc.

                            row.repairAction || raw[13] || '',

                            raw[14] ?? '', // Customer Company

                            raw[15] ?? '', // Customer Name

                            raw[16] ?? '', // PIC Name

                            raw[17] ?? '', // City

                            raw[18] ?? '', // Phone No.

                            raw[19] ?? '', // Mobile No.

                            formatExcelDateOnly(raw[20]), // Start Repair Date

                            raw[21] ?? '', // TAT Case

                            formatExcelDateOnly(raw[22]), // Finish Date

                            formatExcelDateOnly(raw[23]), // Closed Date

                            row.labor === '' ?
                            '' :
                            Number(row.labor),

                            row.keterangan || ''

                        ]);

                    });


                    const ws =
                        XLSX.utils.aoa_to_sheet(
                            aoa
                        );


                    // =====================================================
                    // LEBAR KOLOM EXCEL
                    // A = 5
                    // B sampai kolom terakhir = 15
                    // =====================================================
                    ws['!cols'] = header.map((h, colIdx) => {
                        return {
                            wch: colIdx === 0 ? 4 : 10
                        };
                    });

                    // [DIUBAH] Filter dimulai dari baris header (bukan baris
                    // judul), pakai referensi range manual supaya tetap
                    // benar walau ada baris judul & baris kosong di atasnya.
                    const lastCol =
                        XLSX.utils.encode_col(header.length - 1);

                    const lastRow =
                        aoa.length;

                    ws['!autofilter'] = {
                        ref: XLSX.utils.encode_cell({
                                r: headerRowIndex,
                                c: 0
                            }) +
                            ':' +
                            lastCol + lastRow
                    };


                    // Border tipis di semua sel tabel (dari baris header sampai
                    // baris data terakhir) + header tebal & abu-abu muda.
                    const thinLine = {
                        style: 'thin',
                        color: {
                            rgb: '000000'
                        }
                    };
                    const cellBorder = {
                        top: thinLine,
                        bottom: thinLine,
                        left: thinLine,
                        right: thinLine
                    };

                    for (let r = headerRowIndex; r < aoa.length; r++) {
                        for (let c = 0; c < header.length; c++) {

                            const addr = XLSX.utils.encode_cell({
                                r: r,
                                c: c
                            });

                            if (!ws[addr]) {
                                ws[addr] = {
                                    t: 's',
                                    v: ''
                                };
                            }

                            ws[addr].s = (r === headerRowIndex) ? {
                                border: cellBorder,
                                font: {
                                    bold: true
                                },
                                fill: {
                                    patternType: 'solid',
                                    fgColor: {
                                        rgb: 'D9D9D9'
                                    }
                                },
                                alignment: {
                                    vertical: 'center'
                                }
                            } : {
                                border: cellBorder,
                                alignment: {
                                    vertical: 'top'
                                }
                            };
                        }
                    }

                    // judul laporan tebal
                    if (ws['A1']) ws['A1'].s = {
                        font: {
                            bold: true,
                            sz: 14
                        }
                    };
                    if (ws['A2']) ws['A2'].s = {
                        font: {
                            bold: true
                        }
                    };


                    const wb =
                        XLSX.utils.book_new();


                    XLSX.utils.book_append_sheet(
                        wb,
                        ws,
                        'Review Part'
                    );


                    XLSX.writeFile(
                        wb,
                        'review_part_po_labor_' +
                        new Date()
                        .toISOString()
                        .slice(0, 10) +
                        '.xlsx'
                    );
                }


