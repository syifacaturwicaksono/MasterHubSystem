@extends('layouts.app')

@section('content')

<style id="fmPageTheme">
    /* TEMA FOOTER MASTER (tampilan saja, tidak mengubah fungsi) */
    .fm-page {
        --x-border: #e5eaf1;
        --x-muted: #64748b;
        --x-ink: #0f172a;
        --x-accent: #2563eb;
    }

    .fm-page .pg-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        padding-bottom: 18px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--x-border);
    }

    .fm-page .pg-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--x-accent);
        margin-bottom: 6px;
    }

    .fm-page .pg-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--x-ink);
        margin: 0 0 4px;
    }

    .fm-page .pg-subtitle {
        font-size: 13.5px;
        color: var(--x-muted);
        margin: 0;
    }

    .fm-page .pg-panel {
        background: #fff;
        border: 1px solid var(--x-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .fm-page .pg-panel-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--x-border);
    }

    .fm-page .pg-panel-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--x-ink);
        margin: 0;
    }

    .fm-page .pg-panel-body {
        padding: 20px 22px;
    }

    .fm-page label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .fm-page .form-control,
    .fm-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 14px;
        color: var(--x-ink);
        box-shadow: none;
    }

    .fm-page .form-control:focus,
    .fm-page .form-select:focus {
        border-color: var(--x-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .fm-page .form-control::placeholder {
        color: #94a3b8;
    }

    .fm-page .btn:not(.note-btn) {
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 18px;
        transition: all .2s ease;
    }

    .fm-page .btn-sm:not(.note-btn) {
        padding: 5px 14px;
        font-size: 13px;
    }

    .fm-page .btn-primary {
        background: var(--x-accent);
        border-color: var(--x-accent);
    }

    .fm-page .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .fm-page .btn-secondary {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .fm-page .btn-secondary:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--x-ink);
    }

    .fm-page .btn-warning:not(.note-btn) {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .fm-page .btn-warning:not(.note-btn):hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--x-ink);
    }

    .fm-page .btn-danger {
        background: #fff;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .fm-page .btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    .fm-page .pg-table-wrap {
        overflow: auto;
        border: 1px solid var(--x-border);
        border-radius: 10px;
    }

    .fm-page .pg-table {
        margin: 0;
        font-size: 13px;
    }

    .fm-page .pg-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--x-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .fm-page .pg-table td {
        padding: 12px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .fm-page .pg-table tbody tr:hover {
        background: #fafbfd;
    }

    .fm-page .pg-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        background: #dbeafe;
        color: #1e40af;
    }

    .fm-page .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }

    .fm-page .modal-header {
        background: #0f172a;
        color: #fff;
        border-bottom: 0;
        padding: 16px 22px;
    }

    .fm-page .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
    }

    .fm-page .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .fm-page .modal-footer {
        border-top: 1px solid var(--x-border);
        background: #f8fafc;
    }

    /* toolbar Summernote: tombol transparan di atas toolbar gelap, ikon putih */
    .fm-page .note-toolbar .note-btn-group > button.note-btn {
        background: transparent !important;
        border: 0 !important;
        color: #fff !important;
        padding: 5px 9px;
        font-size: 13px;
        font-weight: 400;
        border-radius: 6px;
        box-shadow: none !important;
    }

    .fm-page .note-toolbar .note-btn-group > button.note-btn:hover,
    .fm-page .note-toolbar .note-btn-group > button.note-btn:focus,
    .fm-page .note-toolbar .note-btn-group > button.note-btn.active {
        background: #334155 !important;
    }
</style>

<div class="container-fluid fm-page">

    <!-- TITLE -->
    <div class="pg-header">

        <div>

            <div class="pg-eyebrow">Master Data</div>

            <h1 class="pg-title">Footer Master</h1>

            <p class="pg-subtitle">
                Kelola footer email system
            </p>

        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addFooterModal">

            Add Footer

        </button>

    </div>

    <!-- ADD MODAL -->
    <div class="modal fade"
        id="addFooterModal"
        tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <form
                    id="addFooterForm"
                    action="/footer-master/store"
                    method="POST">

                    @csrf

                    <div class="modal-header">

                        <h5 class="modal-title">

                            Tambah Footer

                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">

                                Nama Footer

                            </label>

                            <input
                                type="text"
                                name="footer_name"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Footer HTML

                            </label>

                            <textarea
                                name="footer_html"
                                id="footer_html_add"
                                rows="10"
                                class="form-control"></textarea>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Close

                        </button>

                        <button
                            class="btn btn-primary">

                            Save Footer

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- TABLE -->
    <div class="pg-panel">

        <div class="pg-panel-head">
            <h2 class="pg-panel-title">List Footer</h2>
        </div>

        <div class="pg-panel-body">

            <div class="pg-table-wrap">

                <table class="table table-hover align-middle pg-table">

                    <thead>

                        <tr>

                            <th width="80">
                                No
                            </th>

                            <th>
                                Footer Name
                            </th>

                            <th width="180">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($footers as $item)

                        <tr>

                            <td>
                                  {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $item->footer_name }}
                            </td>

                            <td>

                              <div class="d-flex gap-2">

                                <button
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editFooter{{ $item->id }}">

                                    Edit

                                </button>

                               @if(auth()->user()->role == 'admin')

<form
    action="/footer-master/delete/{{ $item->id }}"
    method="POST"
    style="display:inline"
    onsubmit="return confirm('Yakin hapus footer?')">

    @csrf
    @method('DELETE')

    <button
        class="btn btn-danger btn-sm">

        Hapus

    </button>

</form>

@endif

                              </div>

                            </td>

                        </tr>

                        <!-- EDIT MODAL -->

                        <div class="modal fade"
                            id="editFooter{{ $item->id }}"
                            tabindex="-1">

                            <div class="modal-dialog modal-lg">

                                <div class="modal-content">

                                    <form
                                        id="editFooterForm{{ $item->id }}"
                                        action="/footer-master/update/{{ $item->id }}"
                                        method="POST">

                                        @csrf
                                        @method('PUT')

                                        <div class="modal-header">

                                            <h5 class="modal-title">

                                                Edit Footer

                                            </h5>

                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal">
                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <div class="mb-3">

                                                <label>
                                                    Nama Footer
                                                </label>

                                                <input
                                                    type="text"
                                                    name="footer_name"
                                                    class="form-control"
                                                    value="{{ $item->footer_name }}"
                                                    required>

                                            </div>

                                            <div class="mb-3">

                                                <label>
                                                    Footer HTML
                                                </label>

                                                <textarea
                                                    name="footer_html"
                                                    id="footer_html_edit_{{ $item->id }}"
                                                    rows="10"
                                                    class="form-control">{{ $item->footer_html }}</textarea>

                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button
                                                type="button"
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal">

                                                Close

                                            </button>

                                            <button
                                                class="btn btn-primary">

                                                Update

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                        @empty

                        <tr>

                            <td colspan="3"
                                class="text-center text-muted py-4">

                                Belum ada footer

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- ★★★ DIUBAH b: <script src=".../bootstrap@5.3.3/...bundle.min.js"> DIHAPUS dari sini.
     Layout (app.blade.php) sudah memuat Bootstrap 5.0.2 duluan, jadi ini duplikat versi beda
     yang menyebabkan semua tombol di dalam modal (termasuk toolbar Summernote) tidak
     merespon klik. Baris ini sudah ada sejak file asli, bukan ditambahkan untuk Summernote. ★★★ -->

<!-- SUMMERNOTE RICH TEXT EDITOR (WYSIWYG gratis, sudah ada tombol Table & Code View) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">

<!-- ★★★ DIUBAH : CSS fix — dropdown Summernote (Font Size, Color, dll) sering ketutup/
     ke-clip kalau editornya ada di dalam modal Bootstrap. Paksa z-index tinggi & overflow visible. ★★★ -->
<!---Yogi-->
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
| SUMMERNOTE FOOTER
|--------------------------------------------------------------------------
*/

const FONT_NAMES = [
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

const FONT_SIZES = [
    '8','9','10','11','12','14',
    '16','18','20','22','24',
    '26','28','32','36','48','64','72'
];

const SUMMERNOTE_TOOLBAR_FOOTER = [
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

function initFooterSummernote(selector) {

    // Hindari inisialisasi ganda
    if ($(selector).next('.note-editor').length) {
        return;
    }

    $(selector).summernote({

        height: 300,

        toolbar: SUMMERNOTE_TOOLBAR_FOOTER,

        fontNames: FONT_NAMES,

        fontNamesIgnoreCheck: FONT_NAMES,

        fontSizes: FONT_SIZES,

        dialogsInBody: true,

        disableDragAndDrop: true,

        callbacks: {

            onInit: function () {

                // Default tanpa spacing
                $(this).next('.note-editor').find('.note-editable').css({
                    lineHeight: '1.2'
                });

                $(this).next('.note-editor').find('.note-editable p').css({
                    marginTop: '0',
                    marginBottom: '0'
                });
            },

            onChange: function (contents) {

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

// ===== ADD FOOTER =====
document.getElementById('addFooterModal')
    ?.addEventListener('shown.bs.modal', function () {

        initFooterSummernote('#footer_html_add');

    });

// ===== EDIT FOOTER =====
document.querySelectorAll('div.modal[id^="editFooter"]').forEach(function(modalEl){

    modalEl.addEventListener('shown.bs.modal', function(){

        const textarea = modalEl.querySelector('textarea[name="footer_html"]');

        if(textarea){
            initFooterSummernote('#' + textarea.id);
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

</script>
<!---/Yogi-->
@endsection