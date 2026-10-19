{{-- [PARTIAL] sparepart_master.scripts.01_helpers_upload_preview — bagian dari sparepart_master.blade.php (baris asli 508-1330) — Script bagian 1/2: variabel, helper, opsi kategori/divisi/harga, upload & preview — FRAGMEN skrip: potongan berurutan dari satu blok <script> (isi satu fungsi DOMContentLoaded) — jangan dipindah/diurutkan ulang --}}
    document.addEventListener('DOMContentLoaded', function() {

        let previewRows = [];

        /*
        |--------------------------------------------------------------------------
        | PENTING
        |--------------------------------------------------------------------------
        | laborCategories sekarang berisi:
        |
        | [
        |   {
        |      name: "LCD",
        |      psg_price: 150000,
        |      ipg_price: 100000
        |   },
        |   ...
        | ]
        |
        | Ini diperlukan agar harga bisa otomatis mengikuti kategori + divisi.
        |--------------------------------------------------------------------------
        */

        let laborCategories = [];

        let divisiOptions = @json($divisiOptions);

        let hargaOptions = @json($hargaOptions);


        const fileInput =
            document.getElementById('smFileInput');

        const uploadBtn =
            document.getElementById('smUploadBtn');

        const errorBox =
            document.getElementById('smUploadError');

        const tbody =
            document.getElementById('smPreviewTbody');

        const summaryText =
            document.getElementById('smSummaryText');

        const confirmBtn =
            document.getElementById('smConfirmBtn');

        const modalEl =
            document.getElementById('smPreviewModal');

        const modal =
            new bootstrap.Modal(modalEl);

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            ).content;


        // ==========================================================
        // ESCAPE HTML
        // ==========================================================

        function escapeHtml(str) {

            const div =
                document.createElement('div');

            div.textContent =
                str == null ? '' : String(str);

            return div.innerHTML;
        }


        // ==========================================================
        // ESCAPE ATTRIBUTE
        // ==========================================================

        function escapeAttr(str) {

            return String(
                    str == null ? '' : str
                )
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

        }


        // ==========================================================
        // FORMAT RUPIAH
        // ==========================================================

        function formatRupiah(num) {

            if (
                num === null ||
                num === undefined ||
                num === ''
            ) {
                return '';
            }

            return 'Rp ' +
                Number(num).toLocaleString('id-ID');

        }


        // ==========================================================
        // CARI DATA KATEGORI
        // ==========================================================

        function findLaborCategory(kategori) {

            if (!kategori) {
                return null;
            }

            const search =
                String(kategori)
                .trim()
                .toLowerCase();

            return laborCategories.find(function(cat) {

                const name =
                    typeof cat === 'object' ?
                    cat.name :
                    cat;

                return String(name)
                    .trim()
                    .toLowerCase() === search;

            }) || null;

        }


        // ==========================================================
        // HARGA OTOMATIS BERDASARKAN
        // KATEGORI + DIVISI
        // ==========================================================

        function getHargaByKategoriDivisi(
            kategori,
            divisi
        ) {

            if (!kategori || !divisi) {
                return '';
            }

            const category =
                findLaborCategory(kategori);

            if (
                !category ||
                typeof category !== 'object'
            ) {
                return '';
            }


            const divisiUpper =
                String(divisi)
                .trim()
                .toUpperCase();


            // PSG
            if (divisiUpper === 'PSG') {

                return (
                        category.psg_price !== null &&
                        category.psg_price !== undefined
                    ) ?
                    category.psg_price :
                    '';

            }


            // IPG
            if (divisiUpper === 'IPG') {

                return (
                        category.ipg_price !== null &&
                        category.ipg_price !== undefined
                    ) ?
                    category.ipg_price :
                    '';

            }


            return '';

        }


        // ==========================================================
        // CATEGORY OPTIONS
        // ==========================================================

        function buildCategoryOptions(
            selectedValue
        ) {

            let html =
                '<option value="">-- pilih kategori --</option>';


            laborCategories.forEach(function(cat) {

                const name =
                    typeof cat === 'object' ?
                    cat.name :
                    cat;


                const isSelected =
                    String(name).trim().toLowerCase() ===
                    String(selectedValue ?? '').trim().toLowerCase() ?
                    'selected' :
                    '';


                html += `
                <option
                    value="${escapeAttr(name)}"
                    ${isSelected}>
                    ${escapeHtml(name)}
                </option>
            `;

            });


            return html;

        }


        // ==========================================================
        // DIVISI OPTIONS
        // ==========================================================

        function buildDivisiOptions(
            selectedValue
        ) {

            let html =
                '<option value="">-- pilih --</option>';


            divisiOptions.forEach(function(div) {

                const isSelected =
                    String(div).trim().toUpperCase() ===
                    String(selectedValue ?? '').trim().toUpperCase() ?
                    'selected' :
                    '';


                html += `
                <option
                    value="${escapeAttr(div)}"
                    ${isSelected}>
                    ${escapeHtml(div)}
                </option>
            `;

            });


            return html;

        }


        // ==========================================================
        // HARGA OPTIONS
        // ==========================================================

        function buildHargaOptions(
            selectedValue
        ) {

            let html =
                '<option value="">-- pilih harga --</option>';


            hargaOptions.forEach(function(h) {

                const isSelected =
                    selectedValue !== null &&
                    selectedValue !== undefined &&
                    selectedValue !== '' &&
                    Number(h) === Number(selectedValue) ?
                    'selected' :
                    '';


                html += `
                <option
                    value="${escapeAttr(h)}"
                    ${isSelected}>
                    ${formatRupiah(h)}
                </option>
            `;

            });


            return html;

        }


        // ==========================================================
        // SET HARGA OTOMATIS
        // ==========================================================

        function updatePreviewHarga(tr) {

            if (!tr) {
                return;
            }


            const kategoriSelect =
                tr.querySelector(
                    '.sm-preview-kat'
                );

            const divisiSelect =
                tr.querySelector(
                    '.sm-preview-divisi'
                );

            const hargaSelect =
                tr.querySelector(
                    '.sm-preview-harga'
                );

            const hargaHint =
                tr.querySelector(
                    '.sm-harga-auto'
                );


            if (
                !kategoriSelect ||
                !divisiSelect ||
                !hargaSelect
            ) {
                return;
            }


            const kategori =
                kategoriSelect.value;

            const divisi =
                divisiSelect.value;


            const harga =
                getHargaByKategoriDivisi(
                    kategori,
                    divisi
                );


            // ======================================================
            // JIKA ADA HARGA
            // ======================================================

            if (harga !== '') {

                /*
                |--------------------------------------------------------------------------
                | Pastikan option harga tersedia.
                | Jika harga kategori belum ada di hargaOptions,
                | tambahkan otomatis ke dropdown.
                |--------------------------------------------------------------------------
                */

                let option =
                    Array.from(
                        hargaSelect.options
                    ).find(function(opt) {

                        return Number(opt.value) ===
                            Number(harga);

                    });


                if (!option) {

                    option =
                        document.createElement('option');

                    option.value =
                        harga;

                    option.textContent =
                        formatRupiah(harga);

                    hargaSelect.appendChild(
                        option
                    );

                }


                hargaSelect.value =
                    harga;


                if (hargaHint) {

                    hargaHint.textContent =
                        '✓ Harga otomatis berdasarkan kategori & divisi';

                }

            }

            // ======================================================
            // JIKA TIDAK ADA HARGA
            // ======================================================
            else {

                hargaSelect.value = '';

                if (hargaHint) {

                    if (kategori && divisi) {

                        hargaHint.textContent =
                            'Harga untuk kategori/divisi ini belum tersedia';

                    } else {

                        hargaHint.textContent =
                            '';

                    }

                }

            }

        }


        // ==========================================================
        // UPLOAD & PREVIEW
        // ==========================================================

        uploadBtn.addEventListener(
            'click',
            function() {

                errorBox.classList.add(
                    'd-none'
                );


                if (!fileInput.files.length) {

                    errorBox.textContent =
                        'Pilih file Excel dulu.';

                    errorBox.classList.remove(
                        'd-none'
                    );

                    return;

                }


                const formData =
                    new FormData();

                formData.append(
                    'file',
                    fileInput.files[0]
                );


                uploadBtn.disabled = true;

                uploadBtn.textContent =
                    'Membaca file...';


                fetch(
                        '{{ route("sparepart-master.preview") }}', {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },

                            body: formData
                        }
                    )

                    .then(function(res) {

                        return res.json()
                            .then(function(data) {

                                return {
                                    status: res.status,
                                    data: data
                                };

                            });

                    })

                    .then(function({
                        status,
                        data
                    }) {

                        uploadBtn.disabled = false;

                        uploadBtn.textContent =
                            '📤 Upload & Preview';


                        if (
                            status !== 200 ||
                            !data.success
                        ) {

                            errorBox.textContent =
                                data.message ||
                                'Gagal membaca file.';

                            errorBox.classList.remove(
                                'd-none'
                            );

                            return;

                        }


                        // ==================================================
                        // SIMPAN DATA PREVIEW
                        // ==================================================

                        previewRows =
                            data.rows || [];


                        /*
                        |--------------------------------------------------------------------------
                        | categories HARUS berisi object:
                        |
                        | {
                        |    name: "...",
                        |    psg_price: ...,
                        |    ipg_price: ...
                        | }
                        |--------------------------------------------------------------------------
                        */

                        laborCategories =
                            data.categories || [];


                        divisiOptions =
                            data.divisiOptions ||
                            divisiOptions;


                        hargaOptions =
                            data.hargaOptions ||
                            hargaOptions;


                        // ==================================================
                        // SUMMARY
                        // ==================================================

                        summaryText.innerHTML =
                            `Total <strong>${data.summary.total}</strong> baris — ` +

                            `<span class="text-danger fw-bold">` +
                            `${data.summary.new} Part Baru` +
                            `</span>, ` +

                            `${data.summary.existing} ` +
                            `sudah ada di database ` +
                            `(akan dilewati saat disimpan).`;


                        // ==================================================
                        // BANGUN TABEL PREVIEW
                        // PART BARU SELALU DI ATAS
                        // ==================================================

                        tbody.innerHTML = [...previewRows]
                            .sort(function(a, b) {

                                // PART BARU → paling atas
                                if (a.is_new && !b.is_new) {
                                    return -1;
                                }

                                // PART LAMA → di bawah
                                if (!a.is_new && b.is_new) {
                                    return 1;
                                }

                                // Sama-sama baru / sama-sama lama
                                // tetap mengikuti urutan Excel
                                return 0;

                            })
                            .map(
                                function(row) {

                                    const unmatchedKategoriHint =
                                        (
                                            !row.kategori_matched &&
                                            row.kategori_excel
                                        ) ?

                                        `
                <div class="sm-kategori-hint">
                    Excel:
                    "${escapeHtml(row.kategori_excel)}"
                    (tidak dikenali, pilih manual)
                </div>
                `

                                        :
                                        '';


                                    const unmatchedDivisiHint =
                                        (
                                            !row.divisi_matched &&
                                            row.divisi_excel
                                        ) ?

                                        `
                <div class="sm-kategori-hint">
                    Excel:
                    "${escapeHtml(row.divisi_excel)}"
                </div>
                `

                                        :
                                        '';


                                    return `

                <tr
                    class="${row.is_new ? 'sm-row-new' : ''}"
                    data-part-number="${escapeAttr(row.part_number)}"
                >

                    <!-- NO -->
                    <td>
                        ${row.no}
                    </td>


                    <!-- PART NUMBER -->
                    <td>

                        <div class="sm-partnumber-cell">

                            <span>
                                ${escapeHtml(row.part_number)}
                            </span>

                            ${
                                row.is_new
                                ?
                                '<span class="sm-badge-new">PART BARU</span>'
                                :
                                ''
                            }

                        </div>

                    </td>


                    <!-- DESKRIPSI -->
                    <td>

                        <input
                            type="text"
                            class="form-control form-control-sm sm-preview-desk"
                            value="${escapeAttr(row.deskripsi || '')}"
                        >

                    </td>


                    <!-- DIVISI -->
                    <td>

                        <select
                            class="form-select form-select-sm sm-preview-divisi"
                        >
                            ${buildDivisiOptions(
                                row.divisi_matched
                            )}
                        </select>

                        ${unmatchedDivisiHint}

                    </td>


                    <!-- KATEGORI -->
                    <td>

                        <select
                            class="form-select form-select-sm sm-preview-kat"
                        >
                            ${buildCategoryOptions(
                                row.kategori_matched
                            )}
                        </select>

                        ${unmatchedKategoriHint}

                    </td>


                    <!-- HARGA -->
                    <td>

                        <select
                            class="form-select form-select-sm sm-preview-harga"
                        >
                            ${buildHargaOptions(
                                row.harga
                            )}
                        </select>

                        <div class="sm-harga-auto">
                            ${
                                row.harga !== null &&
                                row.harga !== undefined &&
                                row.harga !== ''
                                    ?
                                    '✓ Harga otomatis berdasarkan kategori & divisi'
                                    :
                                    ''
                            }
                        </div>

                    </td>

                </tr>

            `;

                                }
                            )
                            .join('');


                        // ==================================================
                        // SET HARGA OTOMATIS SETELAH TABEL SELESAI
                        // ==================================================

                        Array.from(
                            tbody.querySelectorAll('tr')
                        ).forEach(function(tr) {

                            updatePreviewHarga(tr);

                        });


                        modal.show();

                    })

                    .catch(function() {

                        uploadBtn.disabled = false;

                        uploadBtn.textContent =
                            '📤 Upload & Preview';

                        errorBox.textContent =
                            'Terjadi kesalahan saat upload. Coba lagi.';

                        errorBox.classList.remove(
                            'd-none'
                        );

                    });

            }
        );


