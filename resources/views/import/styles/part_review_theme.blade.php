{{-- [PARTIAL] import.styles.part_review_theme — bagian dari import.blade.php (baris asli 917-1301) — CSS tema modern modal review Part --}}
        <style id="pcModernTheme">
            /* =====================================================================
               TEMA MODERN modal Review RR & Review Part  (TAMPILAN SAJA - tidak
               mengubah fungsi). Semua selector di-scope ke #partClassificationModal.
               ===================================================================== */
            #partClassificationModal {
                --pc-primary: #2563eb;
                --pc-primary-dark: #1d4ed8;
                --pc-ink: #0f172a;
                --pc-muted: #64748b;
                --pc-line: #e2e8f0;
                font-family: Inter, "Segoe UI", system-ui, -apple-system, Roboto, "Helvetica Neue", Arial, sans-serif;
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
            }

            #partClassificationModal .modal-content {
                background: #f1f5f9;
                border: 0;
                border-radius: 0;
                color: var(--pc-ink);
            }

            /* ---------- Header ---------- */
            #partClassificationModal .modal-header {
                background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%) !important;
                color: #fff;
                border: 0;
                padding: .8rem 1.25rem !important;
            }

            #partClassificationModal .modal-title {
                display: flex;
                align-items: center;
                gap: .7rem;
                font-size: 1.05rem;
                font-weight: 700;
                letter-spacing: .2px;
                color: #fff;
            }

            #partClassificationModal .pc-title-ico {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: rgba(255, 255, 255, .16);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
            }

            #partClassificationModal .pc-title-ico svg {
                width: 19px;
                height: 19px;
            }

            #partClassificationModal .modal-title .badge {
                background: rgba(255, 255, 255, .18) !important;
                color: #fff !important;
                font-weight: 600;
                font-size: .72rem;
                letter-spacing: .3px;
                border-radius: 999px;
                padding: .42em .85em;
            }

            #partClassificationModal .btn-close {
                filter: invert(1) grayscale(100%) brightness(200%);
                opacity: .85;
            }

            /* ---------- Body ---------- */
            #partClassificationModal .modal-body {
                background: #f1f5f9;
                padding: 1rem 1.25rem;
            }

            #partClassificationModal #reviewStep2 .modal-body {
                padding: .7rem 1rem;
            }

            #partClassificationModal .pc-callout {
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                border-left: 4px solid var(--pc-primary);
                color: #1e3a8a;
                border-radius: 10px;
                padding: .7rem .95rem;
                font-size: .85rem;
                line-height: 1.5;
                margin-bottom: .75rem;
            }

            /* ---------- Toolbar ---------- */
            #partClassificationModal .review-toolbar {
                background: #fff;
                border: 1px solid var(--pc-line);
                border-radius: 12px;
                padding: .55rem .7rem;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
            }

            #partClassificationModal .pc-search {
                position: relative;
                flex: 1 1 220px;
                max-width: 320px;
            }

            #partClassificationModal .pc-search .pc-search-ico {
                position: absolute;
                left: .65rem;
                top: 50%;
                transform: translateY(-50%);
                width: 15px;
                height: 15px;
                color: #94a3b8;
                pointer-events: none;
            }

            #partClassificationModal .pc-search input {
                padding-left: 2rem;
                width: 100%;
            }

            /* ---------- Form control ---------- */
            #partClassificationModal .form-select,
            #partClassificationModal .form-control {
                border-color: #cbd5e1;
                border-radius: 9px;
                font-size: .84rem;
                color: var(--pc-ink);
                background-color: #fff;
            }

            #partClassificationModal .form-select:focus,
            #partClassificationModal .form-control:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .18);
            }

            /* ---------- Tombol ---------- */
            #partClassificationModal .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                font-weight: 600;
                border-radius: 10px;
                padding: .5rem 1.05rem;
                transition: background-color .15s ease, box-shadow .15s ease, transform .15s ease;
            }

            #partClassificationModal .btn.btn-sm {
                padding: .36rem .75rem;
                font-size: .82rem;
                border-radius: 9px;
            }

            #partClassificationModal .btn .pc-ico {
                width: 1.1em;
                height: 1.1em;
                flex: none;
            }

            #partClassificationModal .btn-primary {
                background: var(--pc-primary);
                border-color: var(--pc-primary);
                box-shadow: 0 4px 12px rgba(37, 99, 235, .28);
            }

            #partClassificationModal .btn-primary:hover {
                background: var(--pc-primary-dark);
                border-color: var(--pc-primary-dark);
                transform: translateY(-1px);
            }

            #partClassificationModal .btn-success {
                background: #059669;
                border-color: #059669;
                box-shadow: 0 4px 12px rgba(5, 150, 105, .28);
            }

            #partClassificationModal .btn-success:hover {
                background: #047857;
                border-color: #047857;
                transform: translateY(-1px);
            }

            #partClassificationModal .btn-outline-dark,
            #partClassificationModal .btn-outline-secondary {
                background: #fff;
                color: #334155;
                border-color: #cbd5e1;
            }

            #partClassificationModal .btn-outline-dark:hover,
            #partClassificationModal .btn-outline-secondary:hover {
                background: #f1f5f9;
                color: var(--pc-ink);
                border-color: #94a3b8;
            }

            #partClassificationModal .btn-outline-primary {
                background: #eff6ff;
                color: var(--pc-primary-dark);
                border-color: #bfdbfe;
            }

            #partClassificationModal .btn-outline-primary:hover {
                background: var(--pc-primary);
                color: #fff;
                border-color: var(--pc-primary);
            }

            /* ---------- Footer ---------- */
            #partClassificationModal .modal-footer {
                background: #fff;
                border-top: 1px solid var(--pc-line);
                padding: .75rem 1.25rem;
                gap: .6rem;
                justify-content: flex-end;
                box-shadow: 0 -4px 12px rgba(15, 23, 42, .05);
            }

            #partClassificationModal .modal-footer>.btn {
                flex: 0 0 auto;
                min-width: 150px;
                margin: 0;
            }

            #partClassificationModal .pc-help {
                margin-right: auto;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                border-radius: 50%;
                color: #2563eb;
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                text-decoration: none;
                transition: background-color .15s ease, color .15s ease;
            }

            #partClassificationModal .pc-help:hover {
                background: #2563eb;
                color: #fff;
            }

            #partClassificationModal .pc-help .pc-ico {
                width: 18px;
                height: 18px;
            }

            /* ---------- Legend (chip) ---------- */
            #partClassificationModal .review-legend {
                gap: .35rem .5rem;
            }

            #partClassificationModal .legend-item {
                background: #f8fafc;
                border: 1px solid var(--pc-line);
                border-radius: 999px;
                padding: .16rem .3rem .16rem .55rem;
                font-size: .78rem;
                font-weight: 500;
                color: #334155;
            }

            #partClassificationModal .legend-item b {
                background: #e2e8f0;
                color: var(--pc-ink);
                border-radius: 999px;
                padding: .02rem .5rem;
                font-size: .72rem;
            }

            #partClassificationModal .legend-dot {
                width: 11px;
                height: 11px;
                border-radius: 50%;
                border: 1px solid rgba(15, 23, 42, .25);
            }

            #partClassificationModal #totalPartSheetText {
                background: #eff6ff;
                color: var(--pc-primary-dark) !important;
                border: 1px solid #bfdbfe;
                border-radius: 999px;
                padding: .28rem .85rem;
                font-weight: 600 !important;
                font-size: .78rem;
            }

            /* ---------- Panel Cara penggunaan ---------- */
            #partClassificationModal .review-guide-panel {
                border: 1px solid var(--pc-line);
                border-radius: 12px;
                box-shadow: 0 16px 40px rgba(15, 23, 42, .22);
                padding: .8rem 1.1rem;
            }

            /* ---------- Tabel ---------- */
            #partClassificationModal .part-review-table-wrapper {
                background: #fff;
                border: 1px solid var(--pc-line);
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
            }

            #partClassificationModal #partClassificationTable,
            #partClassificationModal #rrTable {
                font-size: .86rem;
                color: var(--pc-ink);
            }

            #partClassificationModal #partClassificationTable thead th,
            #partClassificationModal #rrTable thead th {
                background-color: #f8fafc !important;
                color: #475569;
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .6px;
                font-weight: 700;
                padding: .75rem .8rem;
                border: 0;
                box-shadow: inset 0 -1px 0 var(--pc-line);
            }

            #partClassificationModal #partClassificationTable.table-bordered>:not(caption)>*,
            #partClassificationModal #rrTable.table-bordered>:not(caption)>* {
                border-width: 0;
            }

            #partClassificationModal #partClassificationTable.table-bordered>:not(caption)>*>*,
            #partClassificationModal #rrTable.table-bordered>:not(caption)>*>* {
                border-width: 0 0 1px 0;
                border-color: rgba(15, 23, 42, .07);
                padding: .6rem .8rem;
            }

            #partClassificationModal #partClassificationTable tbody tr:hover>td {
                filter: brightness(.975);
            }

            #partClassificationModal #partClassificationTable td a {
                color: var(--pc-primary-dark);
                text-decoration: none;
            }

            #partClassificationModal #partClassificationTable td a:hover {
                text-decoration: underline;
            }

            #partClassificationModal #partClassificationTable td .badge.bg-secondary {
                border-radius: 999px;
                padding: .35em .7em;
                font-weight: 600;
            }

            #partClassificationModal .apo-review-badge-baru {
                border-radius: 999px;
                font-size: .68rem;
                font-weight: 600;
                padding: .3em .65em;
            }

            /* ---------- Scrollbar tipis ---------- */
            #partClassificationModal .part-review-table-wrapper::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }

            #partClassificationModal .part-review-table-wrapper::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 999px;
                border: 2px solid #fff;
            }

            #partClassificationModal .part-review-table-wrapper::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
