{{-- [PARTIAL] sparepart_master.styles.page — bagian dari sparepart_master.blade.php (baris asli 5-166) — CSS halaman Sparepart Master --}}
<style>
    .sm-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 6px;
    }

    .sm-header-icon {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0ea5e9, #0369a1);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.25);
    }

    .sm-header-icon svg {
        width: 24px;
        height: 24px;
        stroke: #fff;
    }

    .sm-title {
        font-weight: 700;
        font-size: 1.5rem;
        margin: 0;
        color: #1a1d23;
    }

    .sm-subtitle {
        color: #6c757d;
        font-size: 0.92rem;
        margin: 2px 0 0;
    }

    .sm-upload-card {
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 22px;
        margin: 20px 0 28px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .sm-row-new {
        background: #fee2e2 !important;
    }

    .sm-badge-new {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #b91c1c;
        background: #fecaca;
        padding: 3px 10px;
        border-radius: 999px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .sm-badge-divisi {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .sm-badge-divisi.ipg {
        color: #0369a1;
        background: #dbeafe;
    }

    .sm-badge-divisi.psg {
        color: #6d28d9;
        background: #ede9fe;
    }

    #smPreviewTableWrap {
        overflow-x: auto;
    }

    #smPreviewModal .modal-dialog {
        max-width: 90vw;
    }

    #smPreviewModal .table {
        font-size: 0.94rem;
        margin-bottom: 0;
        min-width: 1000px;
    }

    #smPreviewModal .table th,
    #smPreviewModal .table td {
        padding: 10px 12px;
        vertical-align: middle;
    }

    #smPreviewModal .table th:nth-child(1),
    #smPreviewModal .table td:nth-child(1) {
        width: 4%;
    }

    #smPreviewModal .table th:nth-child(2),
    #smPreviewModal .table td:nth-child(2) {
        width: 16%;
    }

    #smPreviewModal .table th:nth-child(3),
    #smPreviewModal .table td:nth-child(3) {
        width: 28%;
    }

    #smPreviewModal .table th:nth-child(4),
    #smPreviewModal .table td:nth-child(4) {
        width: 13%;
    }

    #smPreviewModal .table th:nth-child(5),
    #smPreviewModal .table td:nth-child(5) {
        width: 24%;
    }

    #smPreviewModal .table th:nth-child(6),
    #smPreviewModal .table td:nth-child(6) {
        width: 15%;
    }

    .sm-partnumber-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .sm-kategori-hint {
        font-size: 0.75rem;
        color: #dc2626;
        margin-top: 4px;
    }

    .sm-harga-auto {
        font-size: 0.72rem;
        color: #198754;
        margin-top: 4px;
        font-weight: 600;
    }

    .sm-edit-btn,
    .sm-save-btn,
    .sm-cancel-btn {
        white-space: nowrap;
    }
</style>
