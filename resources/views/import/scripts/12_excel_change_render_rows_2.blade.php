{{-- [PARTIAL] import.scripts.12_excel_change_render_rows_2 — bagian dari import.blade.php (baris asli 7122-7628) — Handler Excel: render baris preview (bagian 2) + error SweetAlert + readAsArrayBuffer — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                                            data.forEach(
                                                (
                                                    value,
                                                    index
                                                ) => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    if (
                                                        [
                                                            1,
                                                            2,
                                                            3,
                                                            4,
                                                            5,
                                                            6,
                                                            7,
                                                            9,
                                                            10,
                                                            11
                                                        ].includes(
                                                            index
                                                        ) &&
                                                        value !== ''
                                                    ) {

                                                        let num =
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
                                                                num
                                                            )
                                                        ) {

                                                            if (
                                                                index ===
                                                                7
                                                            ) {

                                                                value =
                                                                    Math.round(
                                                                        num
                                                                    );

                                                            } else {

                                                                value =
                                                                    num
                                                                    .toFixed(
                                                                        2
                                                                    )
                                                                    .replace(
                                                                        '.',
                                                                        ','
                                                                    );

                                                            }

                                                        }

                                                    }


                                                    if (
                                                        index ===
                                                        8 &&
                                                        value !==
                                                        ''
                                                    ) {

                                                        let num =
                                                            parseFloat(
                                                                String(
                                                                    value
                                                                )
                                                                .replace(
                                                                    '%',
                                                                    ''
                                                                )
                                                                .replace(
                                                                    ',',
                                                                    '.'
                                                                )
                                                            );


                                                        if (
                                                            !isNaN(
                                                                num
                                                            )
                                                        ) {

                                                            value =
                                                                Math.round(
                                                                    num
                                                                ) +
                                                                '%';

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
                                            // REMINDER
                                            // ============================================

                                        } else if (
                                            type ===
                                            'reminder'
                                        ) {

                                            const map = [
                                                1,
                                                2,
                                                4,
                                                7,
                                                8,
                                                9,
                                                10
                                            ];


                                            map.forEach(
                                                excelIndex => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    td.innerText =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );


                                            // ============================================
                                            // PEMBAYARAN
                                            // ============================================

                                        } else if (
                                            type ===
                                            'pembayaran'
                                        ) {

                                            const map = [
                                                2,
                                                1,
                                                3,
                                                4,
                                                5,
                                                7,
                                                9
                                            ];


                                            map.forEach(
                                                excelIndex => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    let value =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    if (
                                                        excelIndex ===
                                                        9
                                                    ) {

                                                        let number =
                                                            parseFloat(
                                                                String(
                                                                    value
                                                                ).replace(
                                                                    /,/g,
                                                                    ''
                                                                )
                                                            );


                                                        if (
                                                            !isNaN(
                                                                number
                                                            )
                                                        ) {

                                                            value =
                                                                'Rp ' +
                                                                number
                                                                .toLocaleString(
                                                                    'id-ID', {
                                                                        minimumFractionDigits: 2,

                                                                        maximumFractionDigits: 2
                                                                    }
                                                                );

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
                                            // KLA
                                            // ============================================

                                        } else if (
                                            type ===
                                            'kla'
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
                                                8,
                                                9
                                            ];


                                            map.forEach(
                                                excelIndex => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    td.innerText =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );


                                            // ============================================
                                            // PO LABOR / APO
                                            // ============================================

                                        } else if (
                                            type ===
                                            'apo'
                                        ) {

                                            const map = [

                                                1, // Case ID
                                                3, // Company Name
                                                4, // Received Date
                                                5, // Warranty Status
                                                6, // Serial No.
                                                7, // Product Tower
                                                8, // Product No.
                                                9, // Product Name
                                                10, // Case Type
                                                20, // Start Repair Date
                                                22, // Finish Date
                                                23, // Closed Date
                                                24 // Labor

                                            ];


                                            map.forEach(
                                                excelIndex => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    let value =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    td.innerText =
                                                        value;


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );


                                            // ========================================
                                            // PART RESULT
                                            // ========================================

                                            const caseIdForLabor =
                                                String(
                                                    row[1] ??
                                                    ''
                                                ).trim();


                                            const winner =
                                                apoCaseWinners[
                                                    caseIdForLabor
                                                ] ?? null;


                                            const laborCols = [

                                                winner?.partNo ??
                                                '-',

                                                winner?.partName ??
                                                '-',

                                                winner?.category ??
                                                '-',

                                                winner?.price !=
                                                null

                                                ?
                                                Number(
                                                    winner.price
                                                ).toLocaleString(
                                                    'id-ID'
                                                )

                                                :
                                                '-'

                                            ];


                                            laborCols.forEach(
                                                value => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    td.innerText =
                                                        value;


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );


                                            // ============================================
                                            // WIP
                                            // ============================================

                                        } else if (
                                            type ===
                                            'wip'
                                        ) {

                                            const map = [

                                                1,
                                                2,
                                                4,
                                                6,
                                                12,
                                                24,
                                                28,
                                                36

                                            ];


                                            map.forEach(
                                                excelIndex => {

                                                    let td =
                                                        document.createElement(
                                                            'td'
                                                        );


                                                    td.innerText =
                                                        row[
                                                            excelIndex
                                                        ] ?? '';


                                                    tr.appendChild(
                                                        td
                                                    );

                                                }
                                            );

                                        }


                                        previewTbody.appendChild(
                                            tr
                                        );

                                    }
                                );


                                table.appendChild(
                                    previewTbody
                                );

                            } catch (error) {

                                console.error(
                                    'Preview Excel error:',
                                    error
                                );

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal membaca file',
                                    text: error.message ||
                                        'File Excel tidak dapat diproses.'
                                });

                            }

                        };


                    reader.readAsArrayBuffer(
                        file
                    );

                }
            );
