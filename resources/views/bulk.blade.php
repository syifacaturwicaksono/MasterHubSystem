@extends('layouts.app')

@section('content')

@include('bulk.styles.page_theme')

<div class="container-fluid bc-page">

    @include('bulk.sections.filter_summary')

    <form method="POST" id="bulkForm" enctype="multipart/form-data">

        @csrf

        <input type="hidden" name="aging_filter" value="{{ request('aging_filter') }}">


        @include('bulk.sections.data_table')

        <!-- MODAL PREVIEW: dirender terpisah di luar tabel utama, agar HTML valid dan tidak merusak tabel utama -->
        @foreach($data as $item)

        @php
        $jenisUpload = request('jenis_upload');

        // 1. Pemetaan tabel dan field berdasarkan jenis upload
        switch ($jenisUpload) {
        case 'W002-TAT14D (Pending case 14 days)':
        $tableNameModal = 'pending_14d';
        $fieldCaseModal = 'case_id';
        break;

        case 'W003-TAT5D (Pending case 5 days)':
        $tableNameModal = 'pending';
        $fieldCaseModal = 'case_id';
        break;

        case 'W004-KCI':
        $tableNameModal = 'kci';
        $fieldCaseModal = 'case_id';
        break;

        case 'W005-FR (Finish Repair)':
        $tableNameModal = 'finish_repair';
        $fieldCaseModal = 'case_id';
        break;

        case 'M002-Reminder Invoice':
        $tableNameModal = 'reminder';
        $fieldCaseModal = 'kode_company';
        break;

        case 'M003-Pembayaran':
        $tableNameModal = 'pembayaran';
        $fieldCaseModal = 'kode_company';
        break;

        case 'D001-KLA':
        $tableNameModal = 'kla';
        $fieldCaseModal = 'case_id';
        break;

        case 'M004-PO Labor':
        $tableNameModal = 'apo';
        $fieldCaseModal = 'case_id';
        break;

        default:
        $tableNameModal = 'wip_datas';
        $fieldCaseModal = 'case_id_manual';
        break;
        }

        // M001-MPR: detail sudah ada langsung di $item (data mpr_datas)
        if ($jenisUpload === 'M001-MPR') {

        $casesModal = collect();
        $totalCaseModal = $item->total_case ?? 0;

        } else {

        // 2. Inisialisasi Query Builder
        $caseQueryModal = DB::table($tableNameModal)
        ->whereNull('sent_at');

        // 3. Filter berdasarkan perusahaan
        if ($jenisUpload === 'M002-Reminder Invoice' || $jenisUpload === 'M003-Pembayaran') {

        // Reminder & Pembayaran pakai kode_company penuh
        $caseQueryModal->where('kode_company', $item->kode_company);

        } elseif ($jenisUpload === 'D001-KLA') {

        // KLA: pakai 1 karakter pertama (D/E/F/G/H)
        $caseQueryModal->where(
        DB::raw("LEFT($fieldCaseModal, 1)"),
        substr($item->kode_company, 0, 1)
        );

        } elseif ($jenisUpload === 'M004-PO Labor') {

        // PO Labor: 3 karakter pertama (kode gabungan seperti ELS COMPUTER
        // mencakup semua awalan Case ID cabangnya)
        $caseQueryModal->whereIn(
        DB::raw("LEFT($fieldCaseModal, 3)"),
        $item->apo_codes ?? \App\Support\CompanyGroup::apoCodes($item->kode_company)
        );

        } else {

        // Default: pakai 3 karakter pertama
        $caseQueryModal->where(
        DB::raw("LEFT($fieldCaseModal, 3)"),
        substr($item->kode_company, 0, 3)
        );

        }

        // 4. Filter opsional: kode_upload
        if (request()->filled('kode_upload')) {
        $caseQueryModal->where('kode_upload', request('kode_upload'));
        }

        // 5. Eksekusi Query berdasarkan jenis upload
        if ($jenisUpload === 'M002-Reminder Invoice') {

        $casesModal = $caseQueryModal->get([
        'partner',
        'jatuh_tempo',
        'kekurangan_dokumen',
        'total'
        ]);

        } elseif ($jenisUpload === 'M003-Pembayaran') {

        $casesModal = $caseQueryModal->get([
        'company_name',
        'no_rek',
        'bank',
        'atas_nama',
        'keterangan',
        'jumlah',
        'total',
        'bukti_pembayaran'
        ]);

        } elseif ($jenisUpload === 'D001-KLA') {

        $casesModal = $caseQueryModal->get([
        $fieldCaseModal,
        'case_id',
        'upload',
        'transaksi'
        ]);

        } elseif ($jenisUpload === 'M004-PO Labor') {

        $casesModal = $caseQueryModal->get([
        'id',                // [DITAMBAHKAN]
        $fieldCaseModal,
        'warranty_status',
        'product_name',
        'product_tower',     // [DITAMBAHKAN]
        'case_status',
        'labor',
        'part_no',            // [DITAMBAHKAN]
        'part_name',          // [DITAMBAHKAN]
        'part_category',      // [DITAMBAHKAN]
        'repair_action',      // [DITAMBAHKAN] kolom "Repair Action"
        'keterangan'          // kolom "Keterangan" (isian dari popup review saat upload)
        ]);

        } else {

        // Filter aging hanya untuk non-reminder
        if (request()->filled('aging_filter')) {

        $filterModal = trim(request('aging_filter'));

        if (preg_match('/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$ /', $filterModal, $matchModal)) {
                $caseQueryModal->where(
                'aging',
                $matchModal[1],
                (float) $matchModal[2]
                );
                }
                }

                $casesModal = $caseQueryModal
                ->orderByDesc('aging')
                ->get([
                $fieldCaseModal,
                'aging',
                'case_status'
                ]);
                }

                $totalCaseModal = $casesModal->count();
                }
                @endphp

                @if($jenisUpload === 'M001-MPR')

                @include('bulk.modals.mpr')

                @elseif(request('jenis_upload') == 'M002-Reminder Invoice')

                @include('bulk.modals.reminder_invoice')

                @elseif($jenisUpload === 'M003-Pembayaran')

                @include('bulk.modals.pembayaran')

                @elseif($jenisUpload === 'D001-KLA')

                @include('bulk.modals.kla')

                @elseif($jenisUpload === 'M004-PO Labor')

                @include('bulk.modals.po_labor')

                @else

                @include('bulk.modals.monitoring')

                @endif

                @endforeach


                @include('bulk.sections.template_footer_panel')
    </form>

    @include('bulk.modals.edit_footer')

    @include('bulk.scripts.selection_and_filters')

    @include('bulk.modals.edit_template')

    @include('bulk.scripts.template_footer_actions')

    @include('bulk.assets.summernote')
    @include('bulk.scripts.summernote_init')
    @endsection