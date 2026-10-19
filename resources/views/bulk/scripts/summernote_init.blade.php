{{-- [PARTIAL] bulk.scripts.summernote_init — bagian dari bulk.blade.php (baris asli 2250-2560) — Inisialisasi Summernote & perilaku modal --}}
    <script>
        const SUMMERNOTE_TOOLBAR_BC = [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ];

        function initSummernoteBC(selector, fontNames, fontSizes) {

            if ($(selector).next('.note-editor').length) {
                return;
            }

            $(selector).summernote({

                height: 400,

                toolbar: SUMMERNOTE_TOOLBAR_BC,

                fontNames: fontNames,

                fontNamesIgnoreCheck: fontNames,

                fontSizes: fontSizes,

                dialogsInBody: true,

                disableDragAndDrop: true,

                callbacks: {

                    onInit: function() {

                        // Default tanpa spacing
                        $(this).next('.note-editor').find('.note-editable').css({
                            lineHeight: '1.2'
                        });

                        $(this).next('.note-editor').find('.note-editable p').css({
                            marginTop: '0',
                            marginBottom: '0'
                        });
                    },

                    onChange: function(contents) {

                        // Paragraf baru tetap tanpa spacing
                        $(this).next('.note-editor').find('.note-editable p').css({
                            marginTop: '0',
                            marginBottom: '0'
                        });

                        $(selector).val(contents);
                    }
                }
            });
        }

        // ===== FONT LIST TEMPLATE =====
        const TEMPLATE_FONTS = [
            'Arial',
            'Arial Black',
            'Bahnschrift',
            'Calibri',
            'Cambria',
            'Comic Sans MS',
            'Courier New',
            'Georgia',
            'Helvetica',
            'Segoe UI',
            'Tahoma',
            'Times New Roman',
            'Trebuchet MS',
            'Verdana'
        ];

        // ===== FONT LIST FOOTER =====
        const FOOTER_FONTS = [
            'Arial',
            'Arial Black',
            'Bahnschrift',
            'Book Antiqua',
            'Calibri',
            'Calibri Light',
            'Cambria',
            'Candara',
            'Century Gothic',
            'Comic Sans MS',
            'Consolas',
            'Courier New',
            'Franklin Gothic Medium',
            'Garamond',
            'Georgia',
            'Gill Sans MT',
            'Helvetica',
            'Segoe UI',
            'Tahoma',
            'Times New Roman',
            'Trebuchet MS',
            'Verdana'
        ];

        // ===== SIZE LIST TEMPLATE =====
        const TEMPLATE_SIZES = [
            '8', '9', '10', '11', '12', '14',
            '16', '18', '20', '22', '24',
            '28', '32', '36', '48', '64', '72'
        ];

        // ===== SIZE LIST FOOTER =====
        const FOOTER_SIZES = [
            '8', '9', '10', '11', '12', '14',
            '16', '18', '20', '22', '24',
            '26', '28', '32', '36', '48',
            '64', '72'
        ];

        // ===== EDIT TEMPLATE =====
        document.getElementById('editTemplateModal')
            ?.addEventListener('shown.bs.modal', function() {

                initSummernoteBC(
                    '#edit_body',
                    TEMPLATE_FONTS,
                    TEMPLATE_SIZES
                );
            });

        // ===== FOOTER =====
        document.getElementById('footerModal')
            ?.addEventListener('shown.bs.modal', function() {

                initSummernoteBC(
                    '#edit_footer_html',
                    FOOTER_FONTS,
                    FOOTER_SIZES
                );
            });

        // ===== Tambahkan opsi "No Spacing" ke dropdown Line Height =====
        $(document).on('click', '.note-height .dropdown-menu', function() {

            if ($(this).find('.note-line-height-no-spacing').length === 0) {

                $(this).prepend(`
            <a href="#"
               class="dropdown-item note-line-height-no-spacing"
               data-value="no-spacing">
                No Spacing
            </a>
            <div class="dropdown-divider"></div>
        `);
            }
        });

        // ===== Saat dipilih, hilangkan spacing paragraf =====
        $(document).on('click', '.note-line-height-no-spacing', function(e) {

            e.preventDefault();

            const $editable = $('.note-editor .note-editable:focus');

            const $target = $editable.length ?
                $editable :
                $('.note-editor .note-editable').last();

            $target.find('p').css({
                marginTop: '0',
                marginBottom: '0',
                lineHeight: '1.2'
            });

            // Simpan perubahan ke textarea
            const $textarea = $target.closest('.note-editor').prev('textarea');

            if ($textarea.length) {
                $textarea.val($target.html());
            }
        });

        // ===== Workaround dropdown Summernote di dalam modal =====
        document.addEventListener('click', function(e) {

            const toggle = e.target.closest('.note-editor .dropdown-toggle');

            if (!toggle) {

                if (!e.target.closest('.note-editor .dropdown-menu')) {

                    document.querySelectorAll('.note-editor .dropdown-menu.show')
                        .forEach(function(m) {
                            m.classList.remove('show');
                        });
                }

                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const menu = toggle.nextElementSibling;

            if (!menu || !menu.classList.contains('dropdown-menu')) {
                return;
            }

            const isOpen = menu.classList.contains('show');

            document.querySelectorAll('.note-editor .dropdown-menu.show')
                .forEach(function(m) {
                    m.classList.remove('show');
                });

            if (!isOpen) {
                menu.classList.add('show');
            }

        }, true);

        // [DITAMBAHKAN] Edit kategori + Labor per case, khusus modal PO Labor
        document.addEventListener('click', function(e) {

            // Klik tombol "Edit" -> tampilkan dropdown + tombol Simpan
            if (e.target.classList.contains('btn-edit-apo-kategori')) {
                const td = e.target.closest('td');
                td.querySelector('.apo-edit-form').classList.remove('d-none');
                e.target.classList.add('d-none');
                return;
            }

            // Klik tombol "Simpan" -> kirim ke server, update tampilan
            if (e.target.classList.contains('btn-save-apo-kategori')) {

                const btn = e.target;
                const id = btn.dataset.id;
                const td = btn.closest('td');
                const select = td.querySelector('.apo-kategori-select');
                const categoryId = select.value;

                if (!categoryId) {
                    alert('Pilih kategori dulu.');
                    return;
                }

                btn.disabled = true;
                btn.innerText = 'Menyimpan...';

                fetch('{{ route("apo.updateLabor") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                        },
                        body: JSON.stringify({ id: id, category_id: categoryId })
                    })
                    .then(res => res.json())
                    .then(data => {

                        if (!data.success) {
                            alert(data.message || 'Gagal menyimpan.');
                            btn.disabled = false;
                            btn.innerText = 'Simpan';
                            return;
                        }

                        const row = document.getElementById('apo-row-' + id);

                        row.querySelector('.apo-kategori-text').innerText = data.category;
                        row.querySelector('.apo-labor-text').innerText =
                            Number(data.labor ?? 0).toLocaleString('id-ID');

                        // ★ FIX: sebelumnya cuma baris yang diupdate, Total Labor di
                        // footer modal tidak pernah dihitung ulang setelah edit —
                        // jadi selalu menampilkan angka lama dari saat modal dibuka.
                        // Sekarang dijumlah ulang dari semua baris yang ada di modal
                        // yang sama persis setelah baris ini berhasil disimpan.
                        const modalContent = row.closest('.modal-content');
                        if (modalContent) {
                            let total = 0;
                            modalContent.querySelectorAll('.apo-labor-text').forEach((cell) => {
                                const raw = cell.innerText.replace(/\./g, '').replace(',', '.').trim();
                                total += parseFloat(raw) || 0;
                            });
                            const totalEl = modalContent.querySelector('.apo-total-labor-value');
                            if (totalEl) {
                                totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
                            }
                        }

                        // Sembunyikan lagi form edit, balik ke tombol Edit
                        td.querySelector('.apo-edit-form').classList.add('d-none');
                        td.querySelector('.btn-edit-apo-kategori').classList.remove('d-none');

                        btn.disabled = false;
                        btn.innerText = 'Simpan';
                    })
                    .catch(() => {
                        alert('Gagal menyimpan (koneksi bermasalah).');
                        btn.disabled = false;
                        btn.innerText = 'Simpan';
                    });
            }
        });
    </script>
