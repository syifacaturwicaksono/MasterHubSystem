{{-- [PARTIAL] bulk.styles.page_theme — bagian dari bulk.blade.php (baris asli 5-262) — CSS tema halaman BC Email --}}
<style id="bcPageTheme">
    /* TEMA HALAMAN BC EMAIL (tampilan saja, tidak mengubah fungsi) */
    .bc-page {
        --bc-border: #e5eaf1;
        --bc-muted: #64748b;
        --bc-ink: #0f172a;
        --bc-accent: #2563eb;
    }

    .bc-page .bc-header {
        padding-bottom: 18px;
        margin-bottom: 22px;
        border-bottom: 1px solid var(--bc-border);
    }

    .bc-page .bc-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--bc-accent);
        margin-bottom: 6px;
    }

    .bc-page .bc-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--bc-ink);
        margin: 0 0 4px;
    }

    .bc-page .bc-subtitle {
        font-size: 13.5px;
        color: var(--bc-muted);
        margin: 0;
    }

    /* kartu */
    .bc-page .card {
        border: 1px solid var(--bc-border) !important;
        border-radius: 12px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04) !important;
    }

    .bc-page .card > .card-body {
        padding: 22px;
    }

    .bc-page .bc-card-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--bc-ink);
        margin: 0 0 16px;
    }

    .bc-page .bc-card-note {
        font-size: 12.5px;
        color: var(--bc-muted);
    }

    /* form */
    .bc-page .form-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .bc-page .form-control,
    .bc-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 14px;
        color: var(--bc-ink);
        box-shadow: none;
    }

    .bc-page .form-control:focus,
    .bc-page .form-select:focus {
        border-color: var(--bc-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .bc-page .form-control::placeholder {
        color: #94a3b8;
    }

    /* tombol utama halaman (bukan tombol di dalam modal) */
    .bc-page .btn:not(.note-btn) {
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 18px;
        transition: all .2s ease;
    }

    .bc-page .btn-sm:not(.note-btn) {
        padding: 5px 14px;
        font-size: 13px;
    }

    .bc-page .btn-primary {
        background: var(--bc-accent);
        border-color: var(--bc-accent);
    }

    .bc-page .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .bc-page .btn-outline-secondary {
        background: #fff;
        border-color: #cbd5e1;
        color: #334155;
    }

    .bc-page .btn-outline-secondary:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--bc-ink);
    }

    .bc-page .btn-warning {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .bc-page .btn-warning:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--bc-ink);
    }

    .bc-page .btn-info {
        background: #fff;
        border: 1px solid var(--bc-accent);
        color: var(--bc-accent);
    }

    .bc-page .btn-info:hover {
        background: rgba(37, 99, 235, .08);
        border-color: var(--bc-accent);
        color: #1d4ed8;
    }

    /* total dipilih */
    .bc-page .bc-count {
        min-width: 64px;
        padding: 10px 18px;
        font-size: 18px;
        font-weight: 700;
        color: var(--bc-accent);
        background: rgba(37, 99, 235, .09);
        border-radius: 10px;
        text-align: center;
        display: inline-block;
    }

    /* tabel utama */
    .bc-page .bc-table-wrap {
        border: 1px solid var(--bc-border);
        border-radius: 10px;
    }

    .bc-page .bc-table {
        margin: 0;
        font-size: 13px;
    }

    .bc-page .bc-table thead th {
        background: #f8fafc !important;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--bc-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .bc-page .bc-table td {
        padding: 11px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .bc-page .bc-table tbody tr:hover {
        background: #fafbfd;
    }

    .bc-page .bc-table input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .bc-page .bc-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        background: #0f172a;
        color: #fff;
    }

    .bc-page .bc-mail {
        display: inline-block;
        margin: 0 4px 4px 0;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        background: #e8f5ee;
        color: #166534;
    }

    /* toolbar Summernote: tombol transparan di atas toolbar gelap, ikon putih */
    .bc-page .note-toolbar .note-btn-group > button.note-btn {
        background: transparent !important;
        border: 0 !important;
        color: #fff !important;
        padding: 5px 9px;
        font-size: 13px;
        font-weight: 400;
        border-radius: 6px;
        box-shadow: none !important;
    }

    .bc-page .note-toolbar .note-btn-group > button.note-btn:hover,
    .bc-page .note-toolbar .note-btn-group > button.note-btn:focus,
    .bc-page .note-toolbar .note-btn-group > button.note-btn.active {
        background: #334155 !important;
    }

    .bc-page hr {
        border-color: var(--bc-border);
        opacity: 1;
    }

    /* header modal: bersih, tanpa warna mencolok */
    .bc-page .modal-header.bg-dark,
    .bc-page .modal-header.bg-primary {
        background: #0f172a !important;
    }

    .bc-page .modal-title {
        font-size: 16px;
        font-weight: 600;
    }
</style>
