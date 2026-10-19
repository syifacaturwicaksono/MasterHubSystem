{{-- [PARTIAL] import.scripts.08_excel_change_po_labor_1 — bagian dari import.blade.php (baris asli 4905-5569) — Handler Excel: blok khusus PO Labor (Case ID, RR, data part, default part) bagian 1 — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                                // ====================================================
                                // KHUSUS PO LABOR
                                // ====================================================

                                if (
                                    jenisUpload ===
                                    'M004-PO Labor'
                                ) {

                                    const partsSheetName =
                                        workbook.SheetNames.find(
                                            s =>
                                            s
                                            .toLowerCase()
                                            .includes(
                                                'parts'
                                            )
                                        );


                                    // ================================================
                                    // SEMUA CASE ID DARI SHEET UTAMA
                                    // ================================================

                                    const mainRows =
                                        excelData.slice(4);

                                    const allCaseIds = [];

                                    // ============================================================
                                    // MAP CASE ID -> PRODUCT TOWER, CASE STATUS, REPAIR ACTION & SERIAL NO
                                    // Product Tower berada di kolom index 7
                                    // Case Status berada di kolom index 11
                                    // Repair Action berada di kolom index 13
                                    // Serial No berada di kolom index 6
                                    // [DITAMBAHKAN] caseCaseStatus & caseRepairAction dipakai supaya
                                    // rule "close repair tanpa part" + kolom Case Status/Repair Action
                                    // di popup preview sinkron dengan ApoImport.php dan sheet aslinya.
                                    // [DITAMBAHKAN] caseSerialNo dipakai untuk deteksi RR di Langkah 1,
                                    // biar gak perlu nunggu fetch /upload/rr dulu (rule sama seperti di
                                    // ImportController::rrPreview() - Serial No sama, Case ID beda,
                                    // dalam 1 file yang sama).
                                    // ============================================================
                                    const caseProductTower = {};
                                    const caseCaseStatus = {};
                                    const caseRepairAction = {};
                                    const caseSerialNo = {};
                                    // [DITAMBAHKAN] dipakai untuk kolom Company/Product Name di tabel RR
                                    const caseCompanyName = {};
                                    const caseProductName = {};
                                    // [DITAMBAHKAN] simpan seluruh baris mentah per Case ID (index sama
                                    // dengan kolom sheet "Transaction Report"), dipakai supaya tombol
                                    // Export bisa keluarkan kolom lengkap (Case ID Manual, Warranty
                                    // Status, Problem Desc, Customer Company/Name, PIC, City, No HP,
                                    // tanggal-tanggal, dll) tanpa harus bikin map terpisah untuk tiap
                                    // kolom.
                                    const caseFullRow = {};

                                    // [DITAMBAHKAN] Warranty Status per Case ID (kolom index 5)
                                    const caseWarrantyStatus = {};

                                    // [DITAMBAHKAN] Case Type per Case ID (kolom index 10)
                                    const caseTypeMap = {};

                                    mainRows.forEach(row => {

                                        const caseId =
                                            String(
                                                row[1] ?? ''
                                            ).trim();

                                        if (!caseId) {
                                            return;
                                        }

                                        // [DITAMBAHKAN] Out Warranty dibuang (tidak dapat Labor),
                                        // KECUALI case 511 Sales Delay ("Out Warranty-SD").
                                        const warrantyStatus =
                                            String(
                                                row[5] ?? ''
                                            ).trim();

                                        if (!isCaseAllowedByWarranty(caseId, warrantyStatus)) {
                                            return;
                                        }

                                        const companyName =
                                            String(
                                                row[3] ?? ''
                                            ).trim();

                                        const productTower =
                                            String(
                                                row[7] ?? ''
                                            ).trim()
                                            .toUpperCase();

                                        const productName =
                                            String(
                                                row[9] ?? ''
                                            ).trim();

                                        const caseStatus =
                                            String(
                                                row[11] ?? ''
                                            ).trim();

                                        const repairAction =
                                            String(
                                                row[13] ?? ''
                                            ).trim();

                                        const serialNo =
                                            String(
                                                row[6] ?? ''
                                            ).trim();

                                        if (!caseWarrantyStatus[caseId]) {
                                            caseWarrantyStatus[caseId] =
                                                warrantyStatus;
                                        }

                                        if (!caseTypeMap[caseId]) {
                                            caseTypeMap[caseId] =
                                                String(row[10] ?? '').trim();
                                        }

                                        if (!caseCompanyName[caseId]) {
                                            caseCompanyName[caseId] =
                                                companyName;
                                        }

                                        if (!caseFullRow[caseId]) {
                                            caseFullRow[caseId] = row;
                                        }

                                        if (!caseProductTower[caseId]) {
                                            caseProductTower[caseId] =
                                                productTower;
                                        }

                                        if (!caseProductName[caseId]) {
                                            caseProductName[caseId] =
                                                productName;
                                        }

                                        if (!caseCaseStatus[caseId]) {
                                            caseCaseStatus[caseId] =
                                                caseStatus;
                                        }

                                        if (!caseRepairAction[caseId]) {
                                            caseRepairAction[caseId] =
                                                repairAction;
                                        }

                                        if (!caseSerialNo[caseId] && serialNo) {
                                            caseSerialNo[caseId] =
                                                serialNo;
                                        }

                                        if (!allCaseIds.includes(caseId)) {
                                            allCaseIds.push(caseId);
                                        }

                                    });

                                    // ============================================================
                                    // [DIUBAH] SET CASE ID YANG TERMASUK RR
                                    // Serial No sama, Case ID beda (>1 case id berbeda per serial no).
                                    // [DIUBAH] Case pertama (repair awal, berdasarkan urutan baris di
                                    // sheet) TIDAK dianggap RR -> tetap dapat Labor seperti biasa.
                                    // Case ke-2, ke-3, dst (repair ulang) BARU dianggap RR -> ditandai
                                    // hijau, Labor dikosongkan, tapi tetap ikut masuk ke tabel/BC.
                                    // ============================================================
                                    // [DITAMBAHKAN] Case lama dari bulan sebelumnya (riwayat <= 30 hari)
                                    const rrHistoryMatches = await fetchRrHistoryMatches(
                                        document.getElementById('excelFile').files[0]
                                    );

                                    const rrCaseIds = (function() {

                                        // pakai array (bukan Set) supaya urutan kemunculan (case pertama
                                        // vs berikutnya) kepakai
                                        const bySerial = {};

                                        Object.keys(caseSerialNo).forEach(caseId => {

                                            const serialNo = caseSerialNo[caseId];

                                            if (!bySerial[serialNo]) {
                                                bySerial[serialNo] = [];
                                            }

                                            if (!bySerial[serialNo].includes(caseId)) {
                                                bySerial[serialNo].push(caseId);
                                            }
                                        });

                                        const result = new Set();

                                        Object.values(bySerial).forEach(caseIdList => {

                                            if (caseIdList.length > 1) {

                                                // index 0 = case pertama/original -> SKIP (bukan RR)
                                                // index 1+ = repair ulang -> masuk RR
                                                caseIdList.slice(1).forEach(caseId => {
                                                    result.add(caseId);
                                                });
                                            }
                                        });

                                        // [DITAMBAHKAN] Case yang punya case lama di riwayat (<= 30 hari) = RR
                                        Object.keys(rrHistoryMatches).forEach(caseId => {
                                            if ((rrHistoryMatches[caseId] || []).length) {
                                                result.add(caseId);
                                            }
                                        });

                                        return result;

                                    })();

                                    // ============================================================
                                    // [DITAMBAHKAN] GRUP RR SIAP TAMPIL. Bentuknya:
                                    // [{ serial_no, cases: [{case_id, company_name, product_tower,
                                    // product_name, case_status, repair_action}] }]
                                    // ============================================================
                                    const clientRrGroups = (function() {

                                        const bySerial = {};

                                        Object.keys(caseSerialNo).forEach(caseId => {

                                            const serialNo = caseSerialNo[caseId];

                                            if (!bySerial[serialNo]) {
                                                bySerial[serialNo] = [];
                                            }

                                            bySerial[serialNo].push({
                                                case_id: caseId,
                                                company_name: caseCompanyName[caseId] ?? '',
                                                product_tower: caseProductTower[caseId] ?? '',
                                                product_name: caseProductName[caseId] ?? '',
                                                case_status: caseCaseStatus[caseId] ?? '',
                                                warranty_status: caseWarrantyStatus[caseId] ?? '',
                                                repair_action: caseRepairAction[caseId] ?? ''
                                            });
                                        });

                                        const groups = [];

                                        Object.keys(bySerial).forEach(serialNo => {

                                            if (bySerial[serialNo].length > 1) {

                                                groups.push({
                                                    serial_no: serialNo,
                                                    cases: bySerial[serialNo]
                                                });
                                            }
                                        });

                                        // [DITAMBAHKAN] Case lama (riwayat) ditaruh paling atas grup
                                        // sebagai referensi (tanpa dropdown); case baru di bawahnya jadi RR.
                                        const groupBySerial = {};

                                        groups.forEach(g => {
                                            groupBySerial[g.serial_no] = g;
                                        });

                                        // selisih hari terkecil per case lama (satu case lama bisa
                                        // cocok ke beberapa case baru)
                                        const histMinDays = {};

                                        Object.keys(rrHistoryMatches).forEach(cid => {
                                            (rrHistoryMatches[cid] || []).forEach(o => {
                                                if (typeof o.days === 'number' &&
                                                    (histMinDays[o.case_id] === undefined || o.days < histMinDays[o.case_id])) {
                                                    histMinDays[o.case_id] = o.days;
                                                }
                                            });
                                        });

                                        Object.keys(rrHistoryMatches).forEach(caseId => {

                                            const serialNo = caseSerialNo[caseId];
                                            const olds = rrHistoryMatches[caseId] || [];

                                            if (!serialNo || !olds.length) {
                                                return;
                                            }

                                            let g = groupBySerial[serialNo];

                                            if (!g) {
                                                g = {
                                                    serial_no: serialNo,
                                                    cases: (bySerial[serialNo] || []).slice()
                                                };
                                                groups.push(g);
                                                groupBySerial[serialNo] = g;
                                            }

                                            const existing = new Set(g.cases.map(c => c.case_id));
                                            const toAdd = olds
                                                .filter(o => !existing.has(o.case_id))
                                                .map(o => ({
                                                    ...o,
                                                    days: histMinDays[o.case_id] ?? o.days
                                                }));

                                            if (toAdd.length) {
                                                g.cases = toAdd.concat(g.cases);
                                            }
                                        });

                                        return groups;

                                    })();


                                    // ================================================
                                    // DATA PART
                                    // ================================================

                                    const candidatesByCase = {};

                                    const uniqueParts = {};


                                    if (
                                        partsSheetName
                                    ) {

                                        const partsData =
                                            XLSX.utils.sheet_to_json(
                                                workbook.Sheets[
                                                    partsSheetName
                                                ], {
                                                    header: 1,
                                                    defval: ''
                                                }
                                            );


                                        const partsRows =
                                            partsData.slice(4);


                                        partsRows.forEach(
                                            (row, rowIdx) => {

                                                const caseId =
                                                    String(
                                                        row[1] ?? ''
                                                    ).trim();


                                                // [DIUBAH] Kalau HP part no (kolom M, index 12) kosong,
                                                // pakai Vendor part no (kolom L, index 11) supaya
                                                // dropdown tidak kosong.
                                                const hpPartNo =
                                                    String(
                                                        row[12] ?? ''
                                                    ).trim();

                                                const vendorPartNo =
                                                    String(
                                                        row[11] ?? ''
                                                    ).trim();

                                                const partNo =
                                                    hpPartNo !== '' ?
                                                    hpPartNo :
                                                    vendorPartNo;


                                                if (
                                                    !caseId ||
                                                    !partNo
                                                ) {
                                                    return;
                                                }


                                                const partName =
                                                    String(
                                                        row[13] ?? ''
                                                    ).trim();


                                                const productTower =
                                                    String(
                                                        row[7] ?? ''
                                                    ).trim();


                                                const status =
                                                    String(
                                                        row[19] ?? ''
                                                    ).trim();


                                                // [DITAMBAHKAN] Part request date (kolom index 30)
                                                // dipakai buat nentuin part MANA yang pertama
                                                // di-order (bukan lagi berdasarkan harga terbesar)
                                                const partRequestDate =
                                                    row[30] ?? '';


                                                if (
                                                    !candidatesByCase[
                                                        caseId
                                                    ]
                                                ) {

                                                    candidatesByCase[
                                                        caseId
                                                    ] = [];

                                                }


                                                candidatesByCase[
                                                    caseId
                                                ].push({

                                                    partNo: partNo,

                                                    partName: partName,

                                                    productTower: productTower,

                                                    status: status,

                                                    partRequestDate: partRequestDate,

                                                    // [DITAMBAHKAN] urutan baris di Excel (0 = paling atas).
                                                    // Di sheet Parts: baris ATAS = order terbaru, baris BAWAH = order pertama.
                                                    rowIndex: rowIdx

                                                });


                                                // ====================================
                                                // PART MASTER UNIK
                                                // ====================================

                                                if (
                                                    !uniqueParts[
                                                        partNo
                                                    ]
                                                ) {

                                                    uniqueParts[
                                                        partNo
                                                    ] = {

                                                        part_no: partNo,

                                                        part_name: partName,

                                                        product_tower: productTower

                                                    };

                                                } else {

                                                    if (
                                                        !uniqueParts[
                                                            partNo
                                                        ].part_name &&
                                                        partName
                                                    ) {

                                                        uniqueParts[
                                                                partNo
                                                            ].part_name =
                                                            partName;

                                                    }


                                                    if (
                                                        !uniqueParts[
                                                            partNo
                                                        ].product_tower &&
                                                        productTower
                                                    ) {

                                                        uniqueParts[
                                                                partNo
                                                            ].product_tower =
                                                            productTower;

                                                    }

                                                }

                                            }
                                        );

                                    }


                                    // ====================================================
                                    // PILIH DEFAULT PART
                                    // ====================================================

                                    // [DITAMBAHKAN] Konversi Part Request Date (serial Excel
                                    // atau string) jadi angka yang bisa dibandingkan. Kosong/
                                    // gak valid dianggap paling akhir (Infinity), supaya part
                                    // yang tanggalnya jelas selalu menang duluan.
                                    function toComparableDate(value) {

                                        if (
                                            value === null ||
                                            value === undefined ||
                                            String(value).trim() === ''
                                        ) {
                                            return Infinity;
                                        }

                                        // Serial Excel -> ms epoch
                                        if (typeof value === 'number') {
                                            return (value - 25569) * 86400000;
                                        }

                                        const str = String(value).trim();

                                        const months = {
                                            jan: 0,
                                            feb: 1,
                                            mar: 2,
                                            apr: 3,
                                            may: 4,
                                            mei: 4,
                                            jun: 5,
                                            jul: 6,
                                            aug: 7,
                                            agu: 7,
                                            agt: 7,
                                            sep: 8,
                                            oct: 9,
                                            okt: 9,
                                            nov: 10,
                                            dec: 11,
                                            des: 11
                                        };

                                        const timeOf = (h, mi, s, ap) => {
                                            let hh = Number(h || 0);
                                            if (ap) {
                                                const isPm = ap.toUpperCase() === 'PM';
                                                if (isPm && hh < 12) hh += 12;
                                                if (!isPm && hh === 12) hh = 0;
                                            }
                                            return [hh, Number(mi || 0), Number(s || 0)];
                                        };

                                        // Format "29 Apr 2026" / "29 Apr 2026 14:22(:10)(AM/PM)"
                                        let m = str.match(
                                            /^(\d{1,2})[\s\-\/.]+([A-Za-z]{3,})[\s\-\/.,]*(\d{4})(?:[\sT,]+(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?)?/i
                                        );

                                        if (m) {
                                            const mon = months[m[2].slice(0, 3).toLowerCase()];
                                            if (mon !== undefined) {
                                                const [hh, mi, ss] = timeOf(m[4], m[5], m[6], m[7]);
                                                return Date.UTC(Number(m[3]), mon, Number(m[1]), hh, mi, ss);
                                            }
                                        }

                                        // Format angka "29/04/2026" (hari dulu) + jam opsional
                                        m = str.match(
                                            /^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})(?:[\sT,]+(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?)?/i
                                        );

                                        if (m) {
                                            const [hh, mi, ss] = timeOf(m[4], m[5], m[6], m[7]);
                                            return Date.UTC(Number(m[3]), Number(m[2]) - 1, Number(m[1]), hh, mi, ss);
                                        }

                                        const parsed = Date.parse(str);

                                        return isNaN(parsed) ?
                                            Infinity :
                                            parsed;
                                    }


                                    function pickDefaultPart(
                                        candidates,
                                        resolved
                                    ) {

                                        if (
                                            !candidates ||
                                            candidates.length === 0
                                        ) {

                                            return null;
                                        }


                                        const defectives =
                                            candidates.filter(
                                                c =>
                                                String(
                                                    c.status ||
                                                    ''
                                                )
                                                .trim()
                                                .toLowerCase() ===
                                                'defective'
                                            );


                                        const pool =
                                            defectives.length > 0 ?
                                            defectives :
                                            candidates;


                                        // [DIUBAH] Sebelumnya pilih part dengan harga PALING
                                        // BESAR. Sekarang pilih part yang PERTAMA di-order,
                                        // yaitu Part Request Date paling AWAL (bukan lagi
                                        // berdasarkan nominal labor).
                                        let best =
                                            null;

                                        let bestDate =
                                            Infinity;


                                        pool.forEach(c => {

                                            const date =
                                                toComparableDate(
                                                    c.partRequestDate
                                                );


                                            if (
                                                !best ||
                                                date < bestDate ||
                                                // [DITAMBAHKAN] tanggal sama -> baris Excel yang lebih BAWAH = order lebih awal
                                                (date === bestDate && (c.rowIndex ?? 0) > (best.rowIndex ?? 0))
                                            ) {

                                                best = c;
                                                bestDate = date;

                                            }

                                        });


                                        return best;
                                    }


