{{-- [PARTIAL] import.styles.part_review_modal — bagian dari import.blade.php (baris asli 577-752) — CSS khusus modal review Part PO Labor --}}
        <style>
            /* KHUSUS modal Review Part PO Labor (Langkah 2) & Review RR (Langkah 1) */
            #partClassificationModal #partClassificationTable thead th,
            #partClassificationModal #rrTable thead th {
                position: sticky;
                top: 0;
                z-index: 20;
                background-color: #f8f9fa !important;
                box-shadow: inset 0 -1px 0 #dee2e6;
            }

            /* Fullscreen: area tabel mengisi sisa tinggi layar, header tabel tetap sticky */
            #partClassificationModal .modal-body {
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            #partClassificationModal .part-review-table-wrapper {
                flex: 1 1 auto;
                min-height: 0;
                overflow: auto;
            }

            /* Tombol footer full lebar */
            #partClassificationModal .modal-footer>.btn {
                flex: 1 1 0;
            }

            /* [DITAMBAHKAN] Kotak Repair Action: tinggi dibatasi, isi panjang bisa di-scroll */
            #partClassificationModal .apo-review-repair-box {
                min-width: 230px;
                max-width: 340px;
                max-height: 96px;
                overflow-y: auto;
                padding: .35rem .5rem;
                background: #fff;
                border: 1px solid #cbd5e1;
                border-radius: .4rem;
                font-size: .82rem;
                line-height: 1.35;
                white-space: normal;
                word-break: break-word;
                text-align: left;
            }

            /* [DITAMBAHKAN] Kolom Kategori lebih pendek */
            #partClassificationModal #reviewStep2 .apo-review-kategori {
                min-width: 180px;
                max-width: 190px;
                font-size: .85rem;
            }

            /* [DITAMBAHKAN] Total Labor di footer Langkah 2 (kiri, samping tombol info) */
            #partClassificationModal #reviewStep2 .modal-footer .pc-help {
                margin-right: .25rem;
            }

            #partClassificationModal .pc-total-labor {
                margin-right: auto;
                display: inline-flex;
                flex-direction: column;
                justify-content: center;
                line-height: 1.15;
                padding: .15rem .6rem;
                border-left: 3px solid var(--pc-primary);
                background: #f1f5f9;
                border-radius: .4rem;
                max-width: 360px;
            }

            #partClassificationModal .pc-total-labor-label {
                font-size: .7rem;
                color: #64748b;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #partClassificationModal .pc-total-labor-value {
                font-size: 1rem;
                color: #0f172a;
            }

            /* [DITAMBAHKAN] Langkah 2: padding lebih tipis supaya tabel lebih luas */
            #partClassificationModal #reviewStep2 .modal-body {
                padding: .5rem .75rem;
            }

            /* Panel Cara penggunaan: melayang di atas tabel (tidak mendorong tabel) */
            #partClassificationModal .review-guide-panel {
                position: absolute;
                top: 100%;
                left: 0;
                z-index: 60;
                width: min(900px, 100%);
                background: #fff;
                border: 1px solid #dee2e6;
                border-radius: .5rem;
                box-shadow: 0 8px 24px rgba(0, 0, 0, .25);
                padding: .6rem 1rem;
            }

            /* Cara penggunaan ringkas */
            #partClassificationModal .review-guide ol {
                margin: 0;
                padding-left: 1.1rem;
            }

            @media (min-width: 992px) {
                #partClassificationModal .review-guide ol {
                    columns: 2;
                    column-gap: 2rem;
                }
            }

            /* Arti warna */
            #partClassificationModal .review-legend {
                display: flex;
                flex-wrap: wrap;
                gap: .35rem 1.25rem;
            }

            #partClassificationModal .legend-item {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
            }

            /* Warna baris tabel Review Part. Urutan = prioritas (paling bawah menang):
               PSG putih < IPG biru < Kuning < Merah < RR hijau < Cancel abu-abu */
            /* [DITAMBAHKAN] Langkah 1 (Review RR): blok warna per Serial No,
               selang-seling kuning & biru */
            #partClassificationModal #rrTable tbody tr.rr-group-yellow>td {
                background-color: #fef9c3 !important;
                box-shadow: none !important;
            }

            #partClassificationModal #rrTable tbody tr.rr-group-blue>td {
                background-color: #dbeafe !important;
                box-shadow: none !important;
            }

            #partClassificationModal #partClassificationTable tbody tr.row-ipg>td {
                background-color: #dbeafe !important;
                box-shadow: none !important;
            }

            #partClassificationModal #partClassificationTable tbody tr.table-warning>td {
                background-color: #fef9c3 !important;
                box-shadow: none !important;
            }

            #partClassificationModal #partClassificationTable tbody tr.table-danger>td {
                background-color: #fee2e2 !important;
                box-shadow: none !important;
            }

            #partClassificationModal #partClassificationTable tbody tr.table-success>td {
                background-color: #dcfce7 !important;
                box-shadow: none !important;
            }

            #partClassificationModal #partClassificationTable tbody tr.row-cancel>td {
                background-color: #e2e8f0 !important;
                box-shadow: none !important;
            }

            #partClassificationModal .legend-dot {
                display: inline-block;
                width: 14px;
                height: 14px;
                border-radius: 3px;
                border: 1px solid rgba(0, 0, 0, .3);
            }
        </style>
