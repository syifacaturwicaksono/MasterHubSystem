{{-- [PARTIAL] import.scripts.09_excel_change_po_labor_2 — bagian dari import.blade.php (baris asli 5570-6239) — Handler Excel: PO Labor bagian 2, preview utama, monitoring type, header — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                                    // [DITAMBAHKAN] Urutkan part tiap case: yang PERTAMA di-order ada di
                                    // paling ATAS dropdown. Patokan Part Request Date (kecil = lebih awal);
                                    // kalau sama/tidak terbaca, baris Excel yang lebih BAWAH dianggap lebih awal.
                                    Object.keys(candidatesByCase).forEach(caseId => {

                                        candidatesByCase[caseId].sort((a, b) => {

                                            const da = toComparableDate(a.partRequestDate);
                                            const db = toComparableDate(b.partRequestDate);

                                            if (da !== db) {
                                                return da < db ? -1 : 1;
                                            }

                                            return (b.rowIndex ?? 0) - (a.rowIndex ?? 0);
                                        });
                                    });


                                    try {

                                        // ============================================
                                        // RESOLVE SEMUA PART UNIK
                                        // ============================================

                                        const resolved =
                                            await checkPartsKnown(
                                                Object.values(
                                                    uniqueParts
                                                )
                                            );


                                        // ============================================
                                        // SEMUA CASE ID
                                        //
                                        // Termasuk case yang TIDAK ADA PART
                                        // ============================================

                                        const caseReviewList =
                                            allCaseIds.map(
                                                caseId => {

                                                    const candidates =
                                                        candidatesByCase[
                                                            caseId
                                                        ] || [];


                                                    // -------------------------------
                                                    // TIDAK ADA PART
                                                    // -------------------------------

                                                    if (candidates.length === 0) {

                                                        const productTower =
                                                            String(
                                                                caseProductTower[caseId] ?? ''
                                                            )
                                                            .trim()
                                                            .toUpperCase();

                                                        const caseStatus =
                                                            String(
                                                                caseCaseStatus[caseId] ?? ''
                                                            )
                                                            .trim()
                                                            .toLowerCase();

                                                        // ========================================================
                                                        // [DIPERBAIKI] CASE TANPA PART + CLOSE REPAIR
                                                        // Sebelumnya: semua case PSG tanpa part otomatis kena
                                                        // kategori "Other Part" Rp50.000, tanpa cek status, dan
                                                        // IPG dibiarkan kosong sama sekali. Ini beda dengan
                                                        // ApoImport.php yang cuma menerapkan override ini kalau
                                                        // case_status mengandung "close repair", pakai kategori
                                                        // "Rebuild, Re install, Recovery", dan tetap menampilkan
                                                        // kategori itu untuk IPG walau laborn-nya null.
                                                        // Sekarang disamakan dengan backend.
                                                        // ========================================================
                                                        let noPartCategoryId = '';
                                                        let noPartCategoryName = '';
                                                        let noPartLabor = null;

                                                        if (caseStatus.includes('close repair')) {

                                                            const rebuildCategory =
                                                                laborCategories.find(
                                                                    cat =>
                                                                    String(cat.name ?? '')
                                                                    .trim()
                                                                    .toLowerCase() ===
                                                                    'rebuild, re install, recovery'
                                                                );

                                                            if (rebuildCategory) {

                                                                noPartCategoryId =
                                                                    rebuildCategory.id;

                                                                noPartCategoryName =
                                                                    rebuildCategory.name;

                                                                // PSG dapat labor sesuai psg_price di labor_categories
                                                                // (biasanya Rp50.000). IPG tetap tampil kategorinya
                                                                // tapi labor dibiarkan null (ipg_price memang NULL).
                                                                noPartLabor =
                                                                    computeLaborPrice(
                                                                        rebuildCategory.id,
                                                                        productTower,
                                                                        caseId
                                                                    );
                                                            }
                                                        }

                                                        return {
                                                            caseId: caseId,

                                                            candidates: [],

                                                            selectedPartNo: '',

                                                            noPart: true,

                                                            productTower: productTower,

                                                            // [DITAMBAHKAN] dipakai untuk kolom Case Status & Repair Action
                                                            caseStatus: caseCaseStatus[caseId] ?? '',
                                                            warrantyStatus: caseWarrantyStatus[caseId] ?? '',
                                                            caseType: caseTypeMap[caseId] ?? '',
                                                            companyName: caseCompanyName[caseId] ?? '',

                                                            repairAction: caseRepairAction[caseId] ?? '',

                                                            categoryId: noPartCategoryId,

                                                            categoryName: noPartCategoryName,

                                                            labor: noPartLabor,

                                                            // [DITAMBAHKAN] true kalau kategori di atas keisi otomatis dari
                                                            // rule "close repair tanpa part" -> dipakai buat blok warna penanda
                                                            autoRebuild:
                                                                !!noPartCategoryId,

                                                            // [DITAMBAHKAN] true kalau case ini bagian dari RR (Serial No
                                                            // sama, Case ID beda, dalam file yang sama) -> dipakai untuk
                                                            // warna hijau di Langkah 1
                                                            isRR: rrCaseIds.has(caseId)
                                                        };
                                                    }


                                                    // -------------------------------
                                                    // ADA PART
                                                    // -------------------------------

                                                    const defaultPart =
                                                        pickDefaultPart(
                                                            candidates,
                                                            resolved
                                                        );


                                                    return {

                                                        caseId: caseId,

                                                        candidates: candidates,

                                                        selectedPartNo: defaultPart ?
                                                            defaultPart.partNo : candidates[0]
                                                            .partNo,

                                                        noPart: false,

                                                        // Product Tower dari sheet utama
                                                        // (kolom Product Tower & warna baris IPG)
                                                        productTower: String(
                                                            caseProductTower[caseId] ?? ''
                                                        ).trim().toUpperCase(),

                                                        // [DITAMBAHKAN] dipakai untuk kolom Case Status & Repair Action
                                                        caseStatus: caseCaseStatus[caseId] ?? '',
                                                        warrantyStatus: caseWarrantyStatus[caseId] ?? '',
                                                        caseType: caseTypeMap[caseId] ?? '',
                                                        companyName: caseCompanyName[caseId] ?? '',

                                                        repairAction: caseRepairAction[caseId] ?? '',

                                                        // [DITAMBAHKAN] true kalau case ini bagian dari RR
                                                        isRR: rrCaseIds.has(caseId)

                                                    };

                                                }
                                            );


                                        // ============================================
                                        // TAMPILKAN REVIEW
                                        // ============================================

                                        if (
                                            caseReviewList.length >
                                            0
                                        ) {

                                            uploadBtn.disabled =
                                                true;


                                            const result =
                                                await showPartClassificationPopup(
                                                    caseReviewList,
                                                    resolved,
                                                    clientRrGroups,
                                                    caseFullRow
                                                );


                                            // ========================================
                                            // SIMPAN CLASSIFICATION
                                            // ========================================

                                            if (
                                                result.classifications &&
                                                result.classifications.length >
                                                0
                                            ) {

                                                const response =
                                                    await fetch(
                                                        '{{ route("upload.savePartClassification") }}', {

                                                            method: 'POST',

                                                            headers: {

                                                                'Content-Type': 'application/json',

                                                                'X-CSRF-TOKEN': document
                                                                    .querySelector(
                                                                        'meta[name="csrf-token"]'
                                                                    )
                                                                    ?.content ??
                                                                    ''

                                                            },

                                                            body: JSON.stringify({

                                                                classifications: result.classifications

                                                            })

                                                        }
                                                    );


                                                if (!response.ok) {

                                                    throw new Error(
                                                        'Gagal menyimpan klasifikasi part.'
                                                    );

                                                }

                                            }


                                            // ========================================
                                            // CASE OVERRIDE
                                            // ========================================

                                            document.getElementById(
                                                    'apoCaseOverrides'
                                                ).value =
                                                JSON.stringify(
                                                    result.caseOverrides || {}
                                                );


                                            apoCaseWinners =
                                                result.winners || {};

                                            const laborOverrides = {};
                                            const keteranganOverrides = {};

                                            Object.keys(apoCaseWinners).forEach(caseId => {
                                                const winner = apoCaseWinners[caseId];

                                                if (
                                                    winner &&
                                                    winner.price !== null &&
                                                    winner.price !== undefined &&
                                                    winner.price !== ''
                                                ) {
                                                    laborOverrides[caseId] = Number(winner.price);
                                                }

                                                if (
                                                    winner &&
                                                    winner.keterangan
                                                ) {
                                                    keteranganOverrides[caseId] = winner.keterangan;
                                                }
                                            });

                                            document.getElementById('apoLaborOverrides').value =
                                                JSON.stringify(laborOverrides);

                                            document.getElementById('apoKeteranganOverrides').value =
                                                JSON.stringify(keteranganOverrides);

                                            uploadBtn.disabled =
                                                false;

                                        }

                                    } catch (err) {

                                        // ============================================
                                        // USER CANCEL
                                        // ============================================

                                        if (
                                            err &&
                                            err.cancelled
                                        ) {

                                            document.getElementById(
                                                'excelFile'
                                            ).value = '';


                                            document.getElementById(
                                                'apoCaseOverrides'
                                            ).value = '';

                                            document.getElementById(
                                                'apoLaborOverrides'
                                            ).value = '';

                                            document.getElementById(
                                                'apoKeteranganOverrides'
                                            ).value = '';


                                            uploadBtn.disabled =
                                                false;


                                            return;

                                        }


                                        console.warn(
                                            'Gagal resolve kategori part:',
                                            err
                                        );


                                        uploadBtn.disabled =
                                            false;

                                    }

                                }


                                // ====================================================
                                // PREVIEW UTAMA
                                // ====================================================

                                let table =
                                    document.getElementById(
                                        'previewTable'
                                    );


                                table.innerHTML =
                                    '';


                                // ====================================================
                                // MONITORING TYPE
                                // ====================================================

                                function getMonitoringType(
                                    jenisUpload
                                ) {

                                    if (
                                        jenisUpload ===
                                        'W001-WIP'
                                    ) {
                                        return 'wip';
                                    }


                                    if (
                                        jenisUpload ===
                                        'W003-TAT5D (Pending case 5 days)'
                                    ) {
                                        return 'pending5d';
                                    }


                                    if (
                                        jenisUpload ===
                                        'W002-TAT14D (Pending case 14 days)'
                                    ) {
                                        return 'pending14d';
                                    }


                                    if (
                                        jenisUpload ===
                                        'W004-KCI'
                                    ) {
                                        return 'kci';
                                    }


                                    if (
                                        jenisUpload ===
                                        'W005-FR (Finish Repair)'
                                    ) {
                                        return 'finishrepair';
                                    }


                                    if (
                                        jenisUpload ===
                                        'M001-MPR'
                                    ) {
                                        return 'mpr';
                                    }


                                    if (
                                        jenisUpload ===
                                        'M002-Reminder Invoice'
                                    ) {
                                        return 'reminder';
                                    }


                                    if (
                                        jenisUpload ===
                                        'M003-Pembayaran'
                                    ) {
                                        return 'pembayaran';
                                    }


                                    if (
                                        jenisUpload ===
                                        'D001-KLA'
                                    ) {
                                        return 'kla';
                                    }


                                    if (
                                        jenisUpload ===
                                        'M004-PO Labor'
                                    ) {
                                        return 'apo';
                                    }


                                    return null;
                                }


                                const type =
                                    getMonitoringType(
                                        jenisUpload
                                    );


                                // ====================================================
                                // HEADER
                                // ====================================================

                                const headerMap = {

                                    wip: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Case ID",
                                        "Company Name",
                                        "Finish Date",
                                        "Case Status",
                                        "HP Part No",
                                        "SO No",
                                        "AWB No Part Return",
                                        "Part In Date"
                                    ],

                                    pending5d: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Case ID",
                                        "Count",
                                        "Received Date",
                                        "Start repair date",
                                        "Company Name",
                                        "Aging",
                                        "Customer name",
                                        "Customer company Hierarchy",
                                        "Case Status",
                                        "CE Name",
                                        "Company City",
                                        "Part name",
                                        "HP part no.",
                                        "Part Request Date",
                                        "SO No",
                                        "ETA Date",
                                        "Part In Date",
                                        "Product No",
                                        "Product Name",
                                        "Toatal Case >5d"
                                    ],

                                    pending14d: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Company Name",
                                        "Aging",
                                        "Case ID",
                                        "Received Date",
                                        "Start repair date",
                                        "Part Request Date",
                                        "Part Order Date",
                                        "ETA Date",
                                        "Part In Date",
                                        "Part In Status",
                                        "SO No",
                                        "HP Part No",
                                        "Case Status",
                                        "Product Tower",
                                        "Product Type",
                                        "Vendor part no.",
                                        "CE Name"
                                    ],

                                    kci: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Case ID",
                                        "Count",
                                        "Company Name",
                                        "Aging",
                                        "Customer Name",
                                        "Customer company Hierarchy - Customer company",
                                        "Case Status",
                                        "CE Name",
                                        "Company City"
                                    ],

                                    finishrepair: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Case ID",
                                        "Count",
                                        "Company Name",
                                        "Aging",
                                        "Customer Name",
                                        "Customer company Hierarchy - Customer company",
                                        "Case Status",
                                        "CE Name",
                                        "Company City"
                                    ],

                                    mpr: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Company Name",
                                        "Case IPG",
                                        "Case PSG",
                                        "Total Case",
                                        "TAT",
                                        "TAT w/o BO",
                                        "OLA",
                                        "RRP",
                                        "RRP %",
                                        "WIP",
                                        "Unupdate WIP",
                                        "Latest Aging",
                                        "Catatan"
                                    ],

                                    reminder: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Kode",
                                        "Periode Closing",
                                        "Partner",
                                        "Case",
                                        "Kekurangan Dokumen",
                                        "Total",
                                        "Jatuh Tempo"
                                    ],

                                    pembayaran: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Kode",
                                        "Partner",
                                        "No Rekening",
                                        "Bank",
                                        "Atas Nama",
                                        "Jumlah",
                                        "Total"
                                    ],

                                    kla: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Timestamp",
                                        "Nomor COF ID",
                                        "Cabang",
                                        "Teknisi",
                                        "Nominal Pembayaran",
                                        "Upload File COF",
                                        "Foto Transaksi",
                                        "Keterangan",
                                        "Tanggal Transaksi",
                                        "Pembayaran"
                                    ],

                                    apo: [
                                        "No",
                                        "Jenis",
                                        "Batch",
                                        "Case ID",
                                        "Company Name",
                                        "Received Date",
                                        "Warranty Status",
                                        "Serial No.",
                                        "Product Tower",
                                        "Product No.",
                                        "Product Name",
                                        "Case Type",
                                        "Start Repair Date",
                                        "Finish Date",
                                        "Closed Date",
                                        "Labor",
                                        "HP Part No",
                                        "Part Name",
                                        "Kategori",
                                        "Harga Labor"
                                    ]

                                };


