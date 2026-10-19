{{-- [PARTIAL] template_master.scripts.summernote — bagian dari template_master.blade.php (baris asli 949-1253) — jQuery + Summernote + init editor + perilaku modal --}}
<!-- ★★★ DIUBAH : script <script src=".../bootstrap@5.3.3/...bundle.min.js"> yang tadinya
    ada di sini DIHAPUS. Ternyata layout (app.blade.php) SUDAH memuat Bootstrap versi 5.0.2
    duluan — jadi Bootstrap ke-load 2x versi beda, dan itu yang bikin semua tombol di dalam
    modal (termasuk toolbar Summernote) tidak merespon klik. Cukup pakai punya layout. ★★★ -->

<!-- ★★★ DIUBAH  (5/5): Seluruh blok mulai dari sini sampai akhir file BARU.
    CDN jQuery + Summernote, fungsi initSummernote(), dan pemanggilannya
    saat modal Add/Edit Template dibuka (shown.bs.modal). ★★★ -->
<!-- SUMMERNOTE RICH TEXT EDITOR (WYSIWYG gratis, sudah ada tombol Table & Code View) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">

<!-- ★★★ DIUBAH : CSS fix — dropdown Summernote (Font Size, Color, dll) sering ketutup/
    ke-clip kalau editornya ada di dalam modal Bootstrap. Paksa z-index tinggi & overflow visible. ★★★ -->
 <!---Yogi--->

<style>
    /* Dropdown Summernote di dalam modal */
    .modal .note-editor .dropdown-menu,
    .modal .note-editor .note-dropdown-menu {
        z-index: 3000 !important;
        max-height: 250px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    /* Khusus Font Name */
    .modal .note-fontname .dropdown-menu {
        max-height: 250px !important;
        overflow-y: auto !important;
    }

    /* Khusus Font Size */
    .modal .note-fontsize .dropdown-menu {
        max-height: 220px !important;
        overflow-y: auto !important;
    }

    /* Scrollbar */
    .modal .dropdown-menu::-webkit-scrollbar {
        width: 8px;
    }

    .modal .dropdown-menu::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal .dropdown-menu::-webkit-scrollbar-thumb {
        background: #999;
        border-radius: 10px;
    }

    .modal .dropdown-menu::-webkit-scrollbar-thumb:hover {
        background: #666;
    }

    .modal-body {
        overflow: visible !important;
    }

    .modal .note-editor {
        position: relative;
        z-index: 1;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

<script>
    document
        .getElementById('filterMonitoring')
        .addEventListener('change', function() {

            let selected = this.value;

            document
                .querySelectorAll('tbody tr[data-monitoring]')
                .forEach(function(row) {

                    if (
                        selected === '' ||
                        row.dataset.monitoring === selected
                    ) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }

                });

        });
</script>

<style>
    /* Default editor tanpa spacing */
    .note-editor .note-editable p {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .note-editor .note-editable {
        line-height: 1.2;
    }
</style>

<script>
/*
|--------------------------------------------------------------------------
| RICH TEXT EDITOR (SUMMERNOTE) untuk Body Template
|--------------------------------------------------------------------------
*/

const SUMMERNOTE_TOOLBAR = [
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

function initSummernote(selector) {

    if ($(selector).next('.note-editor').length) {
        return;
    }

    $(selector).summernote({
        height: 400,
        toolbar: SUMMERNOTE_TOOLBAR,

        fontNames: [
            'Arial',
            'Arial Black',
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
        ],

        fontNamesIgnoreCheck: [
            'Arial',
            'Arial Black',
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
        ],

        fontSizes: [
            '8','9','10','11','12','14',
            '16','18','20','22','24',
            '28','32','36','48','64','72'
        ],

        dialogsInBody: true,
        disableDragAndDrop: true,

        callbacks: {

            onInit: function () {

                // Default editor tanpa spacing
                $(this).next('.note-editor').find('.note-editable').css({
                    lineHeight: '1.2'
                });

                $(this).next('.note-editor').find('.note-editable p').css({
                    marginTop: '0',
                    marginBottom: '0'
                });
            },

            onChange: function (contents) {

                // Pastikan paragraf baru tetap tanpa spacing
                $(this).next('.note-editor').find('.note-editable p').css({
                    marginTop: '0',
                    marginBottom: '0'
                });

                $(selector).val(contents);
            }
        }
    });
}

// ===== EDITOR ADD TEMPLATE =====
document.getElementById('addTemplateModal')
    ?.addEventListener('shown.bs.modal', function () {
        initSummernote('#body_add');
    });

// ===== EDITOR EDIT TEMPLATE =====
document.querySelectorAll('[id^="editModal"]').forEach(function (modalEl) {

    modalEl.addEventListener('shown.bs.modal', function () {

        const textarea = modalEl.querySelector('textarea[name="body"]');

        if (textarea) {
            initSummernote('#' + textarea.id);
        }
    });
});

// ===== Tambahkan opsi "No Spacing" ke dropdown Line Height =====
$(document).on('click', '.note-height .dropdown-menu', function () {

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
$(document).on('click', '.note-line-height-no-spacing', function (e) {

    e.preventDefault();

    const $editable = $('.note-editor .note-editable:focus');

    const $target = $editable.length
        ? $editable
        : $('.note-editor .note-editable').last();

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
</script>

<!-- Workaround dropdown Summernote di dalam modal -->
<script>
document.addEventListener('click', function (e) {

    const toggle = e.target.closest('.note-editor .dropdown-toggle');

    if (!toggle) {

        if (!e.target.closest('.note-editor .dropdown-menu')) {

            document.querySelectorAll('.note-editor .dropdown-menu.show')
                .forEach(function (m) {
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
        .forEach(function (m) {
            m.classList.remove('show');
        });

    if (!isOpen) {
        menu.classList.add('show');
    }

}, true);
</script>
