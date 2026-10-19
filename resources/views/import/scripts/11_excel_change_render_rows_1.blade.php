{{-- [PARTIAL] import.scripts.11_excel_change_render_rows_1 — bagian dari import.blade.php (baris asli 6493-7121) — Handler Excel: render baris preview per kolom (bagian 1) — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                                // ====================================================
                                // RENDER PREVIEW
                                // ====================================================

                                previewRows.forEach(
                                    (row, index) => {

                                        if (
                                            row.length === 0
                                        ) {
                                            return;
                                        }


                                        let tr =
                                            document.createElement(
                                                'tr'
                                            );


                                        // ============================================
                                        // NO
                                        // ============================================

                                        let tdNo =
                                            document.createElement(
                                                'td'
                                            );


                                        tdNo.innerText =
                                            index + 1;


                                        tdNo.style.fontWeight =
                                            'bold';


                                        tr.appendChild(
                                            tdNo
                                        );


                                        // ============================================
                                        // JENIS
                                        // ============================================

                                        let tdJenis =
                                            document.createElement(
                                                'td'
                                            );


                                        tdJenis.innerText =
                                            jenisUpload;


                                        tdJenis.style.fontWeight =
                                            'bold';


                                        tr.appendChild(
                                            tdJenis
                                        );


                                        // ============================================
                                        // BATCH
                                        // ============================================

                                        let tdBatch =
                                            document.createElement(
                                                'td'
                                            );


                                        tdBatch.innerText =
                                            document.getElementById(
                                                'kodeUpload'
                                            ).value;


                                        tdBatch.style.color =
                                            '#2563eb';


                                        tr.appendChild(
                                            tdBatch
                                        );


                                        // ============================================
                                        // PENDING 5D
                                        // ============================================

                                        if (
                                            type ===
                                            'pending5d'
                                        ) {

                                            for (
                                                let i = 0; i <= 19; i++
                                            ) {

                                                let td =
                                                    document.createElement(
                                                        'td'
                                                    );


                                                let value =
                                                    row[i] ?? '';


                                                if (
                                                    [
                                                        2,
                                                        3,
                                                        13,
                                                        15,
                                                        16
                                                    ].includes(
                                                        i
                                                    ) &&
                                                    typeof value ===
                                                    'number'
                                                ) {

                                                    let date =
                                                        XLSX.SSF
                                                        .parse_date_code(
                                                            value
                                                        );


                                                    if (date) {

                                                        value =
                                                            String(
                                                                date.d
                                                            ).padStart(
                                                                2,
                                                                '0'
                                                            ) +
                                                            '/' +
                                                            String(
                                                                date.m
                                                            ).padStart(
                                                                2,
                                                                '0'
                                                            ) +
                                                            '/' +
                                                            date.y;

                                                    }

                                                }


                                                if (i === 4) {

                                                    let aging =
                                                        parseFloat(
                                                            String(
                                                                value
                                                            ).replace(
                                                                ',',
                                                                '.'
                                                            )
                                                        );


                                                    if (
                                                        !isNaN(
                                                            aging
                                                        ) &&
                                                        aging >= 5
                                                    ) {

                                                        td.style.backgroundColor =
                                                            '#fecaca';

                                                        td.style.fontWeight =
                                                            'bold';

                                                    }

                                                }


                                                td.innerText =
                                                    value;


                                                tr.appendChild(
                                                    td
                                                );

                                            }


                                            // ============================================
                                            // PENDING 14D
                                            // ============================================

                                        } else if (
                                            type ===
                                            'pending14d'
                                        ) {

                                            for (
                                                let i = 0; i <= 16; i++
                                            ) {

                                                let td =
                                                    document.createElement(
                                                        'td'
                                                    );


                                                let value =
                                                    row[i] ?? '';


                                                if (
                                                    [
                                                        3,
                                                        4,
                                                        5,
                                                        6,
                                                        7,
                                                        8
                                                    ].includes(
                                                        i
                                                    ) &&
                                                    typeof value ===
                                                    'number'
                                                ) {

                                                    let date =
                                                        XLSX.SSF
                                                        .parse_date_code(
                                                            value
                                                        );


                                                    if (date) {

                                                        value =
                                                            String(
                                                                date.d
                                                            ).padStart(
                                                                2,
                                                                '0'
                                                            ) +
                                                            '/' +
                                                            String(
                                                                date.m
                                                            ).padStart(
                                                                2,
                                                                '0'
                                                            ) +
                                                            '/' +
                                                            date.y;

                                                    }

                                                }


                                                if (
                                                    i === 1
                                                ) {

                                                    let aging =
                                                        parseFloat(
                                                            String(
                                                                value
                                                            ).replace(
                                                                ',',
                                                                '.'
                                                            )
                                                        );


                                                    if (
                                                        !isNaN(
                                                            aging
                                                        ) &&
                                                        aging >= 14
                                                    ) {

                                                        td.style.backgroundColor =
                                                            '#fecaca';

                                                        td.style.fontWeight =
                                                            'bold';

                                                    }

                                                }


                                                td.innerText =
                                                    value;


                                                tr.appendChild(
                                                    td
                                                );

                                            }


                                            // ============================================
                                            // KCI
                                            // ============================================

                                        } else if (
                                            type ===
                                            'kci'
                                        ) {

                                            const map = [
                                                0,
                                                1,
                                                2,
                                                3,
                                                4,
                                                5,
                                                6,
                                                7,
                                                8
                                            ];


                                            map.forEach(
                                                (
                                                    excelIndex,
                                                    indexTable
                                                ) => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    let value =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    if (
                                                        indexTable ===
                                                        3
                                                    ) {

                                                        let aging =
                                                            parseFloat(
                                                                value
                                                            );


                                                        if (
                                                            !isNaN(
                                                                aging
                                                            )
                                                        ) {

                                                            aging =
                                                                Math.round(
                                                                    aging
                                                                );


                                                            value =
                                                                aging;


                                                            if (
                                                                aging >=
                                                                5
                                                            ) {

                                                                td.style.backgroundColor =
                                                                    '#fecaca';

                                                                td.style.fontWeight =
                                                                    'bold';

                                                            }

                                                        }

                                                    }


                                                    td.innerText =
                                                        value;


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );


                                            // ============================================
                                            // FINISH REPAIR
                                            // ============================================

                                        } else if (
                                            type ===
                                            'finishrepair'
                                        ) {

                                            const map = [
                                                0,
                                                1,
                                                2,
                                                3,
                                                4,
                                                5,
                                                6,
                                                7,
                                                8
                                            ];


                                            map.forEach(
                                                (
                                                    excelIndex,
                                                    indexTable
                                                ) => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    let value =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    if (
                                                        indexTable ===
                                                        3
                                                    ) {

                                                        let aging =
                                                            parseFloat(
                                                                value
                                                            );


                                                        if (
                                                            !isNaN(
                                                                aging
                                                            )
                                                        ) {

                                                            aging =
                                                                Math.round(
                                                                    aging
                                                                );


                                                            value =
                                                                aging;


                                                            if (
                                                                aging >=
                                                                5
                                                            ) {

                                                                td.style.backgroundColor =
                                                                    '#fecaca';

                                                                td.style.fontWeight =
                                                                    'bold';

                                                            }

                                                        }

                                                    }


                                                    td.innerText =
                                                        value;


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );


                                            // ============================================
                                            // MPR
                                            // ============================================

                                        } else if (
                                            type ===
                                            'mpr'
                                        ) {

                                            const findCol =
                                                prefix => {

                                                    const key =
                                                        Object.keys(
                                                            col
                                                        ).find(
                                                            k =>
                                                            k.startsWith(
                                                                prefix
                                                            )
                                                        );


                                                    return key ?
                                                        col[key] :
                                                        undefined;

                                                };


                                            const data = [

                                                row[
                                                    col[
                                                        'COMPANY NAME'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'CASE IPG'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'CASE PSG'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'TOTAL CASE'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'TAT'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'TAT W/O BO'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'OLA'
                                                    ]
                                                ] ?? '',

                                                findCol('RRP') !==
                                                undefined ?
                                                row[
                                                    findCol(
                                                        'RRP'
                                                    )
                                                ] ?? '' :
                                                '',

                                                row[
                                                    col[
                                                        'RRP%'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'WIP'
                                                    ]
                                                ] ?? '',

                                                findCol(
                                                    'UNUPDATE'
                                                ) !==
                                                undefined ?
                                                row[
                                                    findCol(
                                                        'UNUPDATE'
                                                    )
                                                ] ?? '' :
                                                '',

                                                row[
                                                    col[
                                                        'LATEST AGING'
                                                    ]
                                                ] ?? '',

                                                row[
                                                    col[
                                                        'CATATAN'
                                                    ]
                                                ] ?? ''

                                            ];


