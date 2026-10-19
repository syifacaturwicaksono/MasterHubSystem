{{-- [PARTIAL] import.scripts.07_rr_history_excel_change_start — bagian dari import.blade.php (baris asli 4639-4904) — Riwayat RR lintas bulan, tombol revisi, awal handler FILE EXCEL CHANGE (baca sheet) — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
        // ============================================================
        // [DITAMBAHKAN] RR LINTAS BULAN (JENDELA 30 HARI)
        // Kirim file ke server -> server cocokkan dengan tabel apo_history
        // (Serial No sama, Received baru - Closed lama <= 30 hari) dan balikkan
        // { case_id: [case lama, ...] }. Gagal/offline = tanpa riwayat (aman).
        // ============================================================
        async function fetchRrHistoryMatches(file) {

            if (!file) {
                return {};
            }

            try {
                const fd = new FormData();
                fd.append('file', file);

                const res = await fetch('/upload/rr-history', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: fd
                });

                if (!res.ok) {
                    return {};
                }

                const json = await res.json();

                return json.matches || {};

            } catch (e) {
                console.warn('Cek riwayat RR gagal', e);
                return {};
            }
        }

        (function() {

            const btn = document.getElementById('historyUploadBtn');
            const input = document.getElementById('historyFile');
            const msg = document.getElementById('historyMsg');

            if (!btn || !input) {
                return;
            }

            btn.addEventListener('click', async function() {

                const file = input.files[0];

                if (!file) {
                    msg.style.color = '#b91c1c';
                    msg.textContent = 'Pilih file Excel riwayat dulu.';
                    return;
                }

                btn.disabled = true;
                msg.style.color = '#475569';
                msg.textContent = 'Menyimpan riwayat...';

                try {
                    const fd = new FormData();
                    fd.append('file', file);

                    const res = await fetch('/upload/history', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    const json = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        throw new Error(json.message || 'Gagal menyimpan riwayat.');
                    }

                    msg.style.color = '#166534';
                    msg.textContent = json.message || 'Riwayat tersimpan.';
                    input.value = '';

                } catch (e) {
                    msg.style.color = '#b91c1c';
                    msg.textContent = e.message || 'Gagal menyimpan riwayat.';
                } finally {
                    btn.disabled = false;
                }
            });
        })();

        const revisiBtnEl = document.getElementById('revisiPoLaborBtn');

        if (revisiBtnEl) {
            revisiBtnEl.onclick = startPoLaborRevision;
        }

// ============================================================
        // FILE EXCEL CHANGE
        // ============================================================

        document.getElementById('excelFile')
            .addEventListener(
                'change',
                function(e) {

                    const file =
                        e.target.files[0];


                    if (!file) {
                        return;
                    }


                    const reader =
                        new FileReader();


                    reader.onload =
                        async function(event) {

                            try {

                                const data =
                                    new Uint8Array(
                                        event.target.result
                                    );


                                const workbook =
                                    XLSX.read(
                                        data, {
                                            type: 'array'
                                        }
                                    );


                                // ========================================
                                // JENIS UPLOAD
                                // ========================================

                                let jenisUpload =
                                    document.getElementById(
                                        'jenisUpload'
                                    ).value;


                                // ========================================
                                // CARI SHEET
                                // ========================================

                                let keywords = [];


                                if (
                                    jenisUpload ===
                                    'M004-PO Labor'
                                ) {

                                    keywords = [
                                        'transaction report',
                                        'transcription report'
                                    ];

                                } else if (
                                    jenisUpload ===
                                    'W001-WIP'
                                ) {

                                    keywords = [
                                        'parts',
                                        'part'
                                    ];

                                }


                                let sheetName;


                                if (
                                    jenisUpload ===
                                    'M001-MPR'
                                ) {

                                    sheetName =
                                        workbook.SheetNames.find(
                                            sheet =>
                                            sheet
                                            .toUpperCase()
                                            .includes(
                                                'MPR'
                                            )
                                        );

                                } else {

                                    sheetName =
                                        workbook.SheetNames.find(
                                            sheet =>
                                            keywords.some(
                                                keyword =>
                                                sheet
                                                .toLowerCase()
                                                .includes(
                                                    keyword
                                                )
                                            )
                                        );

                                }


                                if (!sheetName) {

                                    sheetName =
                                        workbook.SheetNames[0];

                                }


                                console.log(
                                    'Sheet =',
                                    sheetName
                                );


                                const sheet =
                                    workbook.Sheets[
                                        sheetName
                                    ];


                                const excelData =
                                    XLSX.utils.sheet_to_json(
                                        sheet, {
                                            header: 1,
                                            defval: ''
                                        }
                                    );


                                // ========================================
                                // RESET PART
                                // ========================================

                                document.getElementById(
                                        'totalPartSheetText'
                                    ).innerText =
                                    'Total Case : 0';


                                let apoCaseWinners = {};


                                const uploadBtn =
                                    document.getElementById(
                                        'uploadSubmitBtn'
                                    );


