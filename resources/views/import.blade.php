@extends('layouts.app')

@section('content')

@include('import.styles.upload_theme')

<div class="container-fluid up-page">

    <!-- TITLE -->
    <div class="up-header">
        <div>
            <div class="up-eyebrow">Data Management</div>
            <h1 class="up-title">Upload Data Excel</h1>
            <p class="up-subtitle">Preview seluruh data excel sebelum diproses.</p>
        </div>
    </div>

    <!-- CARD -->
    <div class="up-card">
        <div class="up-card-head">
            <div>
                <h2 class="up-card-title">Unggah File</h2>
                <p class="up-card-note">Pilih jenis monitoring, beri nama batch, lalu unggah file Excel.</p>
            </div>
        </div>
        <div class="up-card-body">

            @include('import.sections.upload_form')

            @include('import.sections.part_review_modal')
        </div>
        @include('import.styles.part_review_modal')
        @include('import.sections.rr_history')

        @include('import.sections.excel_preview')

        @include('import.sections.upload_history')

    </div>

    @include('import.sections.loading_overlay')

        @include('import.styles.part_review_theme')
    <style>
        #previewTable th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            z-index: 2;
        }

        #previewTable td,
        #previewTable th {
            padding: 6px 10px;
        }
    </style>

    <!-- XLSX -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>

    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SUCCESS -->
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2000
        });
    </script>
    @endif

    <!-- AUTO KODE -->
    <script>
        document.getElementById('jenisUpload').addEventListener('change', function() {

            let jenis = this.value;

            let tanggal = new Date();

            let bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
            let hari = String(tanggal.getDate()).padStart(2, '0');

            let kode = '';

            switch (jenis) {

                case 'W001-WIP':
                    kode = 'WIP';
                    break;

                case 'W002-TAT14D (Pending case 14 days)':
                    kode = 'TAT14D';
                    break;

                case 'W003-TAT5D (Pending case 5 days)':
                    kode = 'TAT5D';
                    break;

                case 'W004-KCI':
                    kode = 'KCI';
                    break;

                case 'W005-FR (Finish Repair)':
                    kode = 'FR';
                    break;
                case 'M001-MPR':
                    kode = 'MPR';
                    break;
                case 'M002-Reminder Invoice':
                    kode = 'INV';
                    break;
                case 'M003-Pembayaran':
                    kode = 'PAY';
                    break;

                case 'D001-KLA': // [DITAMBAHKAN]
                    kode = 'KLA';
                    break;

                case 'M004-PO Labor': // [DITAMBAHKAN]
                    kode = 'PO';
                    break;


            }

            document.getElementById('kodeUpload').value =
                kode + bulan + hari;

        });
    </script>

    <!-- PREVIEW EXCEL -->
    <!-- PREVIEW EXCEL -->
    <!-- PREVIEW EXCEL -->
    <script>
        @include('import.scripts.01_labor_helpers')
        @include('import.scripts.02_popup_review_part1')
                @include('import.scripts.03_popup_review_render_cases')
                @include('import.scripts.04_popup_review_legend_filter')
                @include('import.scripts.05_popup_review_collect_export')
                @include('import.scripts.06_popup_review_steps_revision')
        @include('import.scripts.07_rr_history_excel_change_start')
                                @include('import.scripts.08_excel_change_po_labor_1')
                                    @include('import.scripts.09_excel_change_po_labor_2')
                                @include('import.scripts.10_excel_change_thead_tbody')
                                @include('import.scripts.11_excel_change_render_rows_1')
                                            @include('import.scripts.12_excel_change_render_rows_2')
    </script>
    <!-- LOADING -->
    <script>
        document.getElementById('uploadForm')
            .addEventListener('submit', function() {

                document.getElementById('loadingOverlay')
                    .style.display = 'flex';

            });
    </script>

    @endsection