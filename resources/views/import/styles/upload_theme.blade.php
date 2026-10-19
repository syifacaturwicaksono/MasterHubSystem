{{-- [PARTIAL] import.styles.upload_theme — bagian dari import.blade.php (baris asli 5-224) — CSS tema halaman Upload --}}
<style id="uploadPageTheme">
    /* TEMA HALAMAN UPLOAD SHEET (tampilan saja, tidak mengubah fungsi) */
    .up-page {
        --up-border: #e5eaf1;
        --up-muted: #64748b;
        --up-ink: #0f172a;
        --up-accent: #2563eb;
    }

    .up-page .up-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding-bottom: 18px;
        margin-bottom: 22px;
        border-bottom: 1px solid var(--up-border);
    }

    .up-page .up-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--up-accent);
        margin-bottom: 6px;
    }

    .up-page .up-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.3px;
        color: var(--up-ink);
        margin: 0 0 4px;
    }

    .up-page .up-subtitle {
        font-size: 13.5px;
        color: var(--up-muted);
        margin: 0;
    }

    .up-page .up-card {
        background: #fff;
        border: 1px solid var(--up-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        margin-bottom: 22px;
    }

    .up-page .up-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 16px 22px;
        border-bottom: 1px solid var(--up-border);
    }

    .up-page .up-card-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--up-ink);
        margin: 0;
    }

    .up-page .up-card-note {
        font-size: 12.5px;
        color: var(--up-muted);
        margin: 2px 0 0;
    }

    .up-page .up-card-body {
        padding: 22px;
    }

    .up-page .up-meta {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--up-muted);
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .up-page .up-link {
        color: var(--up-accent);
        font-weight: 500;
        text-decoration: none;
        border-bottom: 1px solid rgba(37, 99, 235, .35);
    }

    .up-page .up-link:hover {
        border-bottom-color: var(--up-accent);
    }

    .up-page .form-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .up-page .form-control,
    .up-page .form-select {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 14px;
        color: var(--up-ink);
        box-shadow: none;
    }

    .up-page .form-control:focus,
    .up-page .form-select:focus {
        border-color: var(--up-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .up-page .form-control::placeholder {
        color: #94a3b8;
    }

    .up-page .up-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding-top: 6px;
    }

    .up-page .up-btn {
        border-radius: 8px;
        padding: 9px 20px;
        font-size: 14px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .up-page .up-btn-primary {
        background: var(--up-accent);
        border: 1px solid var(--up-accent);
        color: #fff;
    }

    .up-page .up-btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .up-page .up-btn-ghost {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .up-page .up-btn-ghost:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--up-ink);
    }

    .up-page .up-table-wrap {
        overflow: auto;
        border: 1px solid var(--up-border);
        border-radius: 10px;
    }

    .up-page .up-table {
        margin: 0;
        font-size: 13px;
    }

    .up-page .up-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--up-border);
        padding: 11px 14px;
        white-space: nowrap;
    }

    .up-page .up-table td {
        padding: 11px 14px;
        color: #334155;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .up-page .up-table tbody tr:hover {
        background: #fafbfd;
    }

    .up-page .up-tag {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    .up-page .up-tag-dark {
        background: #0f172a;
        color: #fff;
    }

    .up-page .up-tag-blue {
        background: #dbeafe;
        color: #1e40af;
    }

    .up-page .up-empty {
        text-align: center;
        color: var(--up-muted);
        padding: 28px 0 !important;
    }
</style>
