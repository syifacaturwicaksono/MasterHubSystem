{{-- [PARTIAL] template_master.styles.page — bagian dari template_master.blade.php (baris asli 5-249) — CSS halaman Template Master --}}
<style id="tmPageTheme">
    /* TEMA MASTER TEMPLATE (tampilan saja, tidak mengubah fungsi) */
    .tm-page {
        --t-border: #e5eaf1;
        --t-muted: #64748b;
        --t-ink: #0f172a;
        --t-accent: #2563eb;
    }

    .tm-page .tm-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        padding-bottom: 18px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--t-border);
    }

    .tm-page .tm-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--t-accent);
        margin-bottom: 6px;
    }

    .tm-page .tm-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--t-ink);
        margin: 0 0 4px;
    }

    .tm-page .tm-subtitle {
        font-size: 13.5px;
        color: var(--t-muted);
        margin: 0;
    }

    /* panel daftar */
    .tm-page .tm-panel {
        background: #fff;
        border: 1px solid var(--t-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .tm-page .tm-panel-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--t-border);
    }

    .tm-page .tm-panel-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--t-ink);
        margin: 0;
    }

    .tm-page .tm-panel-body {
        padding: 20px 22px;
    }

    /* form (halaman & modal) */
    .tm-page label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .tm-page .form-control,
    .tm-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 14px;
        color: var(--t-ink);
        box-shadow: none;
    }

    .tm-page .form-control:focus,
    .tm-page .form-select:focus {
        border-color: var(--t-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    /* tombol (tombol toolbar Summernote tidak disentuh) */
    .tm-page .btn:not(.note-btn) {
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 18px;
        transition: all .2s ease;
    }

    .tm-page .btn-sm:not(.note-btn) {
        padding: 5px 14px;
        font-size: 13px;
    }

    .tm-page .btn-primary {
        background: var(--t-accent);
        border-color: var(--t-accent);
    }

    .tm-page .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .tm-page .btn-secondary {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .tm-page .btn-secondary:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--t-ink);
    }

    .tm-page .btn-warning:not(.note-btn) {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .tm-page .btn-warning:not(.note-btn):hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--t-ink);
    }

    .tm-page .btn-danger {
        background: #fff;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .tm-page .btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    /* tabel */
    .tm-page .tm-table-wrap {
        overflow: auto;
        border: 1px solid var(--t-border);
        border-radius: 10px;
    }

    .tm-page .tm-table {
        margin: 0;
        font-size: 13px;
    }

    .tm-page .tm-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--t-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .tm-page .tm-table td {
        padding: 12px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .tm-page .tm-table tbody tr:hover {
        background: #fafbfd;
    }

    .tm-page .tm-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: #dbeafe;
        color: #1e40af;
    }

    /* modal */
    .tm-page .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }

    .tm-page .modal-header {
        background: #0f172a;
        color: #fff;
        border-bottom: 0;
        padding: 16px 22px;
    }

    .tm-page .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
    }

    .tm-page .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .tm-page .modal-footer {
        border-top: 1px solid var(--t-border);
        background: #f8fafc;
    }

    /* toolbar Summernote: tombol transparan di atas toolbar gelap, ikon putih */
    .tm-page .note-toolbar .note-btn-group > button.note-btn {
        background: transparent !important;
        border: 0 !important;
        color: #fff !important;
        padding: 5px 9px;
        font-size: 13px;
        font-weight: 400;
        border-radius: 6px;
        box-shadow: none !important;
    }

    .tm-page .note-toolbar .note-btn-group > button.note-btn:hover,
    .tm-page .note-toolbar .note-btn-group > button.note-btn:focus,
    .tm-page .note-toolbar .note-btn-group > button.note-btn.active {
        background: #334155 !important;
    }
</style>
