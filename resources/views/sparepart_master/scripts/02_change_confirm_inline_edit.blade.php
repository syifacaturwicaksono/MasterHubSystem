{{-- [PARTIAL] sparepart_master.scripts.02_change_confirm_inline_edit — bagian dari sparepart_master.blade.php (baris asli 1331-1833) — Script bagian 2/2: perubahan kategori/divisi/harga, konfirmasi simpan, edit inline — FRAGMEN skrip: potongan berurutan dari satu blok <script> (isi satu fungsi DOMContentLoaded) — jangan dipindah/diurutkan ulang --}}
        // ==========================================================
        // ★★ KATEGORI / DIVISI BERUBAH
        // ★★ HARGA OTOMATIS BERUBAH
        // ==========================================================

        tbody.addEventListener(
            'change',
            function(e) {

                const tr =
                    e.target.closest(
                        'tr[data-part-number]'
                    );


                if (!tr) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Jika KATEGORI berubah
                |--------------------------------------------------------------------------
                */

                if (
                    e.target.classList.contains(
                        'sm-preview-kat'
                    )
                ) {

                    updatePreviewHarga(tr);

                }


                /*
                |--------------------------------------------------------------------------
                | Jika DIVISI berubah
                |--------------------------------------------------------------------------
                */

                if (
                    e.target.classList.contains(
                        'sm-preview-divisi'
                    )
                ) {

                    updatePreviewHarga(tr);

                }

            }
        );


        // ==========================================================
        // KONFIRMASI & SIMPAN
        // ==========================================================

        confirmBtn.addEventListener(
            'click',
            function() {

                const finalRows =
                    Array.from(
                        tbody.querySelectorAll('tr')
                    )
                    .map(function(tr) {

                        return {

                            part_number: tr.dataset.partNumber,

                            deskripsi: tr.querySelector(
                                '.sm-preview-desk'
                            ).value,

                            divisi: tr.querySelector(
                                '.sm-preview-divisi'
                            ).value,

                            kategori: tr.querySelector(
                                '.sm-preview-kat'
                            ).value,

                            harga: tr.querySelector(
                                '.sm-preview-harga'
                            ).value

                        };

                    });


                confirmBtn.disabled = true;

                confirmBtn.textContent =
                    'Menyimpan...';


                fetch(
                        '{{ route("sparepart-master.store") }}', {
                            method: 'POST',

                            headers: {

                                'X-CSRF-TOKEN': csrfToken,

                                'Content-Type': 'application/json'

                            },

                            body: JSON.stringify({
                                rows: finalRows
                            })

                        }
                    )

                    .then(function(res) {

                        return res.json();

                    })

                    .then(function(data) {

                        confirmBtn.disabled = false;

                        confirmBtn.textContent =
                            '✅ Konfirmasi & Simpan Part Baru';


                        if (data.success) {

                            alert(
                                `Berhasil! ` +
                                `${data.inserted} Part Number baru ditambahkan, ` +
                                `${data.skipped} dilewati (sudah ada).`
                            );


                            window.location.reload();

                        } else {

                            alert(
                                'Gagal menyimpan data.'
                            );

                        }

                    })

                    .catch(function() {

                        confirmBtn.disabled = false;

                        confirmBtn.textContent =
                            '✅ Konfirmasi & Simpan Part Baru';


                        alert(
                            'Terjadi kesalahan saat menyimpan.'
                        );

                    });

            }
        );


        // ==========================================================
        // EDIT INLINE DATA TERSIMPAN
        // ==========================================================

        const savedTable =
            document.getElementById(
                'smSavedTable'
            );


        savedTable.addEventListener(
            'click',
            function(e) {

                const tr =
                    e.target.closest(
                        'tr[data-id]'
                    );


                if (!tr) {
                    return;
                }


                const viewDesk =
                    tr.querySelector(
                        '.sm-view-desk'
                    );

                const editDesk =
                    tr.querySelector(
                        '.sm-edit-desk'
                    );


                const viewDivisi =
                    tr.querySelector(
                        '.sm-view-divisi'
                    );

                const editDivisi =
                    tr.querySelector(
                        '.sm-edit-divisi'
                    );


                const viewKat =
                    tr.querySelector(
                        '.sm-view-kat'
                    );

                const editKat =
                    tr.querySelector(
                        '.sm-edit-kat'
                    );


                const viewHarga =
                    tr.querySelector(
                        '.sm-view-harga'
                    );

                const editHarga =
                    tr.querySelector(
                        '.sm-edit-harga'
                    );


                const editBtn =
                    tr.querySelector(
                        '.sm-edit-btn'
                    );

                const saveBtn =
                    tr.querySelector(
                        '.sm-save-btn'
                    );

                const cancelBtn =
                    tr.querySelector(
                        '.sm-cancel-btn'
                    );


                // ==================================================
                // TOGGLE EDIT
                // ==================================================

                function toggleEditMode(
                    isEditing
                ) {

                    viewDesk.classList.toggle(
                        'd-none',
                        isEditing
                    );

                    editDesk.classList.toggle(
                        'd-none',
                        !isEditing
                    );


                    viewDivisi.classList.toggle(
                        'd-none',
                        isEditing
                    );

                    editDivisi.classList.toggle(
                        'd-none',
                        !isEditing
                    );


                    viewKat.classList.toggle(
                        'd-none',
                        isEditing
                    );

                    editKat.classList.toggle(
                        'd-none',
                        !isEditing
                    );


                    viewHarga.classList.toggle(
                        'd-none',
                        isEditing
                    );

                    editHarga.classList.toggle(
                        'd-none',
                        !isEditing
                    );


                    editBtn.classList.toggle(
                        'd-none',
                        isEditing
                    );

                    saveBtn.classList.toggle(
                        'd-none',
                        !isEditing
                    );

                    cancelBtn.classList.toggle(
                        'd-none',
                        !isEditing
                    );

                }


                // ==================================================
                // TOMBOL EDIT
                // ==================================================

                if (e.target === editBtn) {

                    toggleEditMode(true);

                }


                // ==================================================
                // TOMBOL BATAL
                // ==================================================

                if (e.target === cancelBtn) {

                    editDesk.value =
                        viewDesk.textContent.trim();


                    /*
                    |--------------------------------------------------------------------------
                    | Ambil divisi dari badge
                    |--------------------------------------------------------------------------
                    */

                    editDivisi.value =
                        viewDivisi.textContent.trim();


                    editKat.value =
                        viewKat.textContent.trim();


                    toggleEditMode(false);

                }


                // ==================================================
                // TOMBOL SIMPAN
                // ==================================================

                if (e.target === saveBtn) {

                    const id =
                        tr.dataset.id;


                    const newDesk =
                        editDesk.value;


                    const newDivisi =
                        editDivisi.value;


                    const newKat =
                        editKat.value;


                    const newHarga =
                        editHarga.value;


                    saveBtn.disabled = true;

                    saveBtn.textContent =
                        'Menyimpan...';


                    fetch(
                            `/sparepart-master/update/${id}`, {
                                method: 'POST',

                                headers: {

                                    'X-CSRF-TOKEN': csrfToken,

                                    'Content-Type': 'application/json'

                                },

                                body: JSON.stringify({

                                    deskripsi: newDesk,

                                    divisi: newDivisi,

                                    kategori: newKat,

                                    harga: newHarga

                                })

                            }
                        )

                        .then(function(res) {

                            return res.json();

                        })

                        .then(function(data) {

                            saveBtn.disabled = false;

                            saveBtn.textContent =
                                '💾 Simpan';


                            if (data.success) {

                                viewDesk.textContent =
                                    newDesk;


                                viewDivisi.innerHTML =
                                    newDivisi

                                    ?

                                    `<span class="sm-badge-divisi ${newDivisi.toLowerCase()}">
                                ${escapeHtml(newDivisi)}
                            </span>`

                                    :

                                    '';


                                viewKat.textContent =
                                    newKat;


                                viewHarga.textContent =
                                    newHarga ?
                                    formatRupiah(newHarga) :
                                    '';


                                toggleEditMode(false);

                            } else {

                                alert(
                                    'Gagal menyimpan perubahan.'
                                );

                            }

                        })

                        .catch(function() {

                            saveBtn.disabled = false;

                            saveBtn.textContent =
                                '💾 Simpan';

                            alert(
                                'Terjadi kesalahan saat menyimpan.'
                            );

                        });

                }

            }
        );

    });
