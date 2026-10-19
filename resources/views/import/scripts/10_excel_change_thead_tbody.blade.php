{{-- [PARTIAL] import.scripts.10_excel_change_thead_tbody — bagian dari import.blade.php (baris asli 6240-6492) — Handler Excel: thead, tbody, tentukan baris preview — FRAGMEN skrip: potongan berurutan dari satu blok <script> — JANGAN dipindah/diurutkan ulang; tidak valid kalau dibuka sendirian --}}
                                const headers =
                                    headerMap[type] || [];


                                // ====================================================
                                // THEAD
                                // ====================================================

                                let thead =
                                    document.createElement(
                                        'thead'
                                    );


                                thead.className =
                                    'table-light';


                                let trHead =
                                    document.createElement(
                                        'tr'
                                    );


                                headers.forEach(head => {

                                    let th =
                                        document.createElement(
                                            'th'
                                        );


                                    th.innerText =
                                        head;


                                    trHead.appendChild(
                                        th
                                    );

                                });


                                thead.appendChild(
                                    trHead
                                );


                                table.appendChild(
                                    thead
                                );


                                // ====================================================
                                // TBODY
                                // ====================================================

                                let previewTbody =
                                    document.createElement(
                                        'tbody'
                                    );


                                // ====================================================
                                // TENTUKAN PREVIEW ROW
                                // ====================================================

                                let previewRows;

                                let col = {};

                                let startRow;


                                if (
                                    type === 'wip' ||
                                    type === 'apo'
                                ) {

                                    previewRows =
                                        excelData.slice(4);

                                } else if (
                                    type === 'pembayaran'
                                ) {

                                    previewRows =
                                        excelData.slice(5);


                                    previewRows =
                                        previewRows.filter(
                                            row => {

                                                if (
                                                    !row[2] ||
                                                    String(
                                                        row[2]
                                                    ).trim() === ''
                                                ) {

                                                    return false;
                                                }


                                                if (
                                                    String(
                                                        row[1]
                                                    )
                                                    .toUpperCase()
                                                    .includes(
                                                        'TOTAL'
                                                    )
                                                ) {

                                                    return false;
                                                }


                                                return true;

                                            }
                                        );

                                } else if (
                                    type === 'mpr'
                                ) {

                                    startRow =
                                        excelData.findIndex(
                                            row =>
                                            row.some(
                                                cell =>
                                                String(
                                                    cell
                                                )
                                                .trim()
                                                .toUpperCase() ===
                                                'COMPANY NAME'
                                            )
                                        );


                                    if (
                                        startRow === -1
                                    ) {

                                        alert(
                                            'Header COMPANY NAME tidak ditemukan'
                                        );

                                        return;
                                    }


                                    const excelHeader =
                                        excelData[
                                            startRow
                                        ];


                                    excelHeader.forEach(
                                        (h, i) => {

                                            col[
                                                String(h)
                                                .trim()
                                                .toUpperCase()
                                            ] = i;

                                        }
                                    );


                                    previewRows = [];


                                    for (
                                        let i =
                                            startRow + 1;

                                        i <
                                        excelData.length;

                                        i++
                                    ) {

                                        let row =
                                            excelData[i];


                                        if (
                                            row[0] &&
                                            String(
                                                row[0]
                                            )
                                            .toUpperCase()
                                            .includes(
                                                'RATA'
                                            )
                                        ) {

                                            break;
                                        }


                                        if (
                                            !row[0] ||
                                            String(
                                                row[0]
                                            ).trim() === ''
                                        ) {

                                            continue;
                                        }


                                        previewRows.push(
                                            row
                                        );

                                    }

                                } else if (
                                    type === 'reminder'
                                ) {

                                    previewRows =
                                        excelData.slice(1);

                                } else {

                                    previewRows =
                                        excelData.slice(1);

                                }


                                // ====================================================
                                // TOTAL CASE
                                // ====================================================

                                const totalCase =
                                    previewRows.length;


                                document.getElementById(
                                        'totalCaseText'
                                    ).innerText =
                                    'Total Case : ' +
                                    totalCase;


