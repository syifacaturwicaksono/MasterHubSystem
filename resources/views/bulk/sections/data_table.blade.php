{{-- [PARTIAL] bulk.sections.data_table — bagian dari bulk.blade.php (baris asli 418-731) — Kartu tabel data company (checkbox, kode, email, preview) --}}
        <!-- DATA -->
        <div class="card">

            <div class="card-body">

                <div class="bc-table-wrap" style="overflow:auto; max-height:600px;">

                    <table class="table table-hover align-middle bc-table">

                        <thead class="table-light" style="
                                                                                position:sticky;
                                                                                top:0;
                                                                                z-index:1;
                                                                            ">

                            <tr>

                                <th width="50">
                                    <input type="checkbox" id="checkAll">
                                </th>

                                <th>
                                    Kode
                                </th>

                                <th>
                                    Company
                                </th>

                                <th>
                                    Email
                                </th>

                                @unless(request('jenis_upload') == 'M001-MPR')
                                <th>
                                    @if(request('jenis_upload') == 'M002-Reminder Invoice')
                                    Total Invoice
                                    @elseif(request('jenis_upload') == 'M003-Pembayaran')
                                    Total Pembayaran
                                    @else
                                    Total Case
                                    @endif
                                </th>
                                @endunless

                                <th width="120">
                                    Preview
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($data as $item)

                            <tr>

                                <!-- CHECKBOX -->
                                <td>

                                    <input type="checkbox" class="bulk-checkbox" name="selected_company[]"
                                        value="{{ $item->kode_company }}">

                                </td>

                                <!-- KODE -->
                                <td>

                                    <span class="bc-tag">

                                        {{ $item->kode_company }}

                                    </span>

                                </td>

                                <!-- COMPANY -->
                                <td>

                                    {{ $item->company_name }}

                                </td>

                                <!-- EMAIL -->
                                <td>
                                    @php
                                    $emailsQuery = DB::table('email_masters');

                                    if (request('jenis_upload') == 'M003-Pembayaran') {
                                    $emailsQuery->where(
                                    DB::raw('LEFT(kode_company,4)'),
                                    substr($item->kode_company, 0, 4)
                                    );
                                    } elseif (request('jenis_upload') == 'M004-PO Labor') {
                                    // Filter berdasarkan kode_company dan kategori 'apo1'
                                    $emailsQuery->where('kode_company', $item->kode_company)
                                    ->where('kategori', 'apo1'); // Sesuaikan nama kolom 'kategori' jika berbeda di DB
                                    } else {
                                    $emailsQuery->where('kode_company', $item->kode_company);
                                    }

                                    $emails = $emailsQuery->pluck('email');
                                    @endphp

                                    @if($emails->count())
                                    @foreach($emails as $email)
                                    <div class="bc-mail">
                                        {{ $email }}
                                    </div>
                                    @endforeach
                                    @else
                                    <span class="text-muted">
                                        Belum ada email
                                    </span>
                                    @endif
                                </td>

                                @php

                                switch (request('jenis_upload')) {

                                case 'W002-TAT14D (Pending case 14 days)':

                                $tableName = 'pending_14d';
                                $fieldCase = 'case_id';
                                break;

                                case 'W003-TAT5D (Pending case 5 days)':

                                $tableName = 'pending';
                                $fieldCase = 'case_id';
                                break;

                                case 'W004-KCI':

                                $tableName = 'kci';
                                $fieldCase = 'case_id';
                                break;

                                case 'W005-FR (Finish Repair)':

                                $tableName = 'finish_repair';
                                $fieldCase = 'case_id';
                                break;

                                case 'M002-Reminder Invoice':

                                $tableName = 'reminder';
                                $fieldCase = 'kode_company';
                                break;

                                case 'M003-Pembayaran':

                                $tableName = 'pembayaran';
                                $fieldCase = 'kode_company';
                                break;

                                case 'D001-KLA':

                                $tableName = 'kla';
                                $fieldCase = 'case_id';
                                break;

                                case 'M004-PO Labor':

                                $tableName = 'apo';
                                $fieldCase = 'case_id';
                                break;

                                default:

                                $tableName = 'wip_datas';
                                $fieldCase = 'case_id_manual';
                                break;
                                }

                                $totalCase = 0;

                                if (
                                request('jenis_upload') != 'M001-MPR' &&
                                request('jenis_upload') != 'M002-Reminder Invoice' &&
                                request('jenis_upload') != 'M003-Pembayaran'
                                ) {

                                $totalCaseQuery = DB::table($tableName)->whereNull('sent_at');

                                // Filter berdasarkan company
                                if (request('jenis_upload') === 'D001-KLA') {

                                // KLA: 1 karakter pertama
                                $totalCaseQuery->where(
                                DB::raw("LEFT($fieldCase,1)"),
                                substr($item->kode_company, 0, 1)
                                );

                                } elseif (request('jenis_upload') === 'M004-PO Labor') {

                                // PO Labor: 3 karakter pertama (kode gabungan seperti ELS COMPUTER
                                // mencakup semua awalan Case ID cabangnya)
                                $totalCaseQuery->whereIn(
                                DB::raw("LEFT($fieldCase,3)"),
                                $item->apo_codes ?? \App\Support\CompanyGroup::apoCodes($item->kode_company)
                                );

                                } else {

                                // Default: 3 karakter pertama
                                $totalCaseQuery->where(
                                DB::raw("LEFT($fieldCase,3)"),
                                substr($item->kode_company, 0, 3)
                                );
                                }

                                // Filter kode upload
                                if (request()->filled('kode_upload')) {
                                $totalCaseQuery->where(
                                'kode_upload',
                                request('kode_upload')
                                );
                                }

                                // Filter aging
                                if (request()->filled('aging_filter')) {

                                $filter = trim(request('aging_filter'));

                                if (
                                preg_match(
                                '/^(>=|<=|>|<|=)\s*(\d+(\.\d+)?)$ /',
                                        $filter,
                                        $match
                                        )
                                        ) {

                                        $totalCaseQuery->where(
                                        'aging',
                                        $match[1],
                                        (float) $match[2]
                                        );
                                        }
                                        }

                                        $totalCase = $totalCaseQuery->count();
                                        }

                                        @endphp


                                        @unless(request('jenis_upload') == 'M001-MPR')
                                        <td class="text-center">
                                            <span class="fw-bold">

                                                @if(request('jenis_upload') == 'M002-Reminder Invoice')

                                                {{ $item->total_invoice ?? 0 }}

                                                @elseif(request('jenis_upload') == 'M003-Pembayaran')

                                                {{ $item->total_pembayaran ?? 0 }}

                                                @elseif(request('jenis_upload') == 'M004-PO Labor')

                                                {{ $item->total_po_labor ?? $totalCase }}

                                                @else

                                                {{ $totalCase }}

                                                @endif

                                            </span>
                                        </td>
                                        @endunless

                                        <!-- PREVIEW -->
                                        <td>

                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#modal{{ $item->kode_company }}">

                                                Preview

                                            </button>

                                        </td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="{{ request('jenis_upload') == 'M001-MPR' ? 4 : 5 }}"
                                    class="text-center text-muted py-4">

                                    Tidak ada data

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
