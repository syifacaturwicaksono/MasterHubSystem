<?php

namespace App\Http\Controllers\Concerns\Import;

use App\Models\EmailTemplate;
use App\Support\CompanyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Halaman Broadcast Email (bulk) beserta query datanya.
 *
 * Dipecah dari ImportController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: bulk()
 */
trait HandlesBulkPage
{
    public function bulk(Request $request)
    {
        // [DITAMBAHKAN] Daftar kategori labor, buat dropdown edit kategori
        // per case di modal PO Labor.
        $laborCategories = DB::table('labor_categories')->orderBy('name')->get();

        $batches = DB::table('upload_batches')
            ->when($request->filled('jenis_upload'), function ($query) use ($request) {
                $query->where('jenis_upload', $request->jenis_upload);
            })
            ->select('kode_upload', DB::raw('MAX(id) as last_id'))
            ->groupBy('kode_upload')
            ->orderByDesc('last_id')
            ->limit(1)
            ->get();

        $templates = EmailTemplate::latest()->get();

        $footers = DB::table('footer_masters')
            ->orderBy('footer_name')
            ->get();

        $data = collect();

        $templates = EmailTemplate::latest()->get();

        $footers = DB::table('footer_masters')
            ->orderBy('footer_name')
            ->get();

        $data = collect();

        // ==========================
        // WIP
        // ==========================
        if ($request->jenis_upload == 'W001-WIP') {

            $query = DB::table('wip_datas')
                ->select(
                    DB::raw('LEFT(case_id_manual,3) as kode_company'),
                    'company_name',
                    DB::raw('MAX(aging) as max_aging')
                )
                ->whereNull('sent_at');

            // FILTER BATCH
            if ($request->kode_upload) {

                $query->where(
                    'kode_upload',
                    $request->kode_upload
                );
            }

            // FILTER KODE COMPANY
            if ($request->kode_company) {

                $query->where(
                    DB::raw('LEFT(case_id_manual,3)'),
                    'like',
                    '%' . $request->kode_company . '%'
                );
            }

            // FILTER CASE ID
            if ($request->case_id) {

                $query->where(
                    'case_id_manual',
                    'like',
                    '%' . $request->case_id . '%'
                );
            }

            // FILTER AGING
            if ($request->filled('aging_filter')) {

                $filter = trim($request->aging_filter);

                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+)$/', $filter, $match)) {

                    $query->where(
                        'aging',
                        $match[1],
                        (int) $match[2]
                    );
                }
            }

            $data = $query
                ->groupBy(
                    DB::raw('LEFT(case_id_manual,3)'),
                    'company_name'
                )
                ->orderByDesc('max_aging')
                ->get();
        }

        // ==========================
        // Pending case 5days
        // ==========================
        elseif ($request->jenis_upload == 'W003-TAT5D (Pending case 5 days)') {

            $query = DB::table('pending')
                ->select(
                    DB::raw('LEFT(case_id,3) as kode_company'),
                    'company_name'
                )
                ->whereNull('sent_at')
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                );

            // FILTER BATCH
            if ($request->kode_upload) {

                $query->where(
                    'kode_upload',
                    $request->kode_upload
                );
            }

            // FILTER KODE COMPANY
            if ($request->kode_company) {

                $query->where(
                    DB::raw('LEFT(case_id,3)'),
                    'like',
                    '%' . $request->kode_company . '%'
                );
            }

            // FILTER CASE ID
            if ($request->case_id) {

                $query->where(
                    'case_id_manual',
                    'like',
                    '%' . $request->case_id . '%'
                );
            }

            // FILTER AGING
            if ($request->filled('aging_filter')) {

                $filter = trim($request->aging_filter);

                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+)$/', $filter, $match)) {

                    $query->where(
                        'aging',
                        $match[1],
                        (int) $match[2]
                    );
                }
            }


            $data = $query
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                )
                ->get();
        }

        // ==========================
        // Pending case 14days
        // ==========================
        elseif ($request->jenis_upload == 'W002-TAT14D (Pending case 14 days)') {

            $query = DB::table('pending_14d')
                ->select(
                    DB::raw('LEFT(case_id,3) as kode_company'),
                    'company_name'
                )
                ->whereNull('sent_at')
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                );

            // FILTER BATCH
            if ($request->kode_upload) {

                $query->where(
                    'kode_upload',
                    $request->kode_upload
                );
            }

            // FILTER KODE COMPANY
            if ($request->kode_company) {

                $query->where(
                    DB::raw('LEFT(case_id,3)'),
                    'like',
                    '%' . $request->kode_company . '%'
                );
            }

            // FILTER CASE ID
            if ($request->case_id) {

                $query->where(
                    'case_id',
                    'like',
                    '%' . $request->case_id . '%'
                );
            }

            // FILTER AGING
            if ($request->filled('aging_filter')) {

                $filter = trim($request->aging_filter);

                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+)$/', $filter, $match)) {

                    $query->where(
                        'aging',
                        $match[1],
                        (int) $match[2]
                    );
                }
            }


            $data = $query->get();
        }
        // KCI  //
        elseif ($request->jenis_upload == 'W004-KCI') {

            $query = DB::table('kci')
                ->select(
                    DB::raw('LEFT(case_id,3) as kode_company'),
                    'company_name'
                )
                ->whereNull('sent_at')
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                );

            // FILTER BATCH
            if ($request->kode_upload) {

                $query->where(
                    'kode_upload',
                    $request->kode_upload
                );
            }

            // FILTER KODE COMPANY
            if ($request->kode_company) {

                $query->where(
                    DB::raw('LEFT(case_id,3)'),
                    'like',
                    '%' . $request->kode_company . '%'
                );
            }

            // FILTER CASE ID
            if ($request->case_id) {

                $query->where(
                    'case_id_manual',
                    'like',
                    '%' . $request->case_id . '%'
                );
            }

            // FILTER AGING
            if ($request->filled('aging_filter')) {

                $filter = trim($request->aging_filter);

                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+)$/', $filter, $match)) {

                    $query->where(
                        'aging',
                        $match[1],
                        (int) $match[2]
                    );
                }
            }


            $data = $query
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                )
                ->get();
        }
        // Finish Repair  //
        elseif ($request->jenis_upload == 'W005-FR (Finish Repair)') {

            $query = DB::table('finish_repair')
                ->select(
                    DB::raw('LEFT(case_id,3) as kode_company'),
                    'company_name'
                )
                ->whereNull('sent_at')
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                );

            // FILTER BATCH
            if ($request->kode_upload) {

                $query->where(
                    'kode_upload',
                    $request->kode_upload
                );
            }

            // FILTER KODE COMPANY
            if ($request->kode_company) {

                $query->where(
                    DB::raw('LEFT(case_id,3)'),
                    'like',
                    '%' . $request->kode_company . '%'
                );
            }

            // FILTER CASE ID
            if ($request->case_id) {

                $query->where(
                    'case_id_manual',
                    'like',
                    '%' . $request->case_id . '%'
                );
            }

            // FILTER AGING
            if ($request->filled('aging_filter')) {

                $filter = trim($request->aging_filter);

                if (preg_match('/^(>=|<=|>|<|=)\s*(\d+)$/', $filter, $match)) {

                    $query->where(
                        'aging',
                        $match[1],
                        (int) $match[2]
                    );
                }
            }


            $data = $query
                ->groupBy(
                    DB::raw('LEFT(case_id,3)'),
                    'company_name'
                )
                ->get();
        }
// MPR //
elseif ($request->jenis_upload == 'M001-MPR') {

    // ★★★ DIUBAH CLAUDE: sebelumnya pakai SELECT ... COUNT(*) as total_case + groupBy,
    // padahal mpr_datas SUDAH berisi 1 baris = 1 ringkasan lengkap per company (kolom
    // case_ipg, case_psg, total_case, tat, tat_wo_bo, ola, rrp, rrp_percent, wip,
    // unupdate_wip, latest_aging, catatan SUDAH tersimpan langsung sebagai kolom).
    // COUNT(*) di atas selalu bernilai 1 (karena tiap company memang cuma 1 baris),
    // dan field lain (case_ipg, tat, dst) tidak ikut ke-select sama sekali — itu
    // penyebab modal Preview selalu menampilkan Total Case: 1 dan sisanya kosong
    // untuk SEMUA company. Sekarang query ini ambil semua kolom apa adanya, tanpa
    // groupBy/agregasi. ★★★
    $query = DB::table('mpr_datas');

    // Filter batch
    if ($request->filled('kode_upload')) {

        $query->where('kode_upload', $request->kode_upload);

    } else {

        // Jika tidak pilih batch, ambil batch terakhir
        $lastBatch = DB::table('upload_batches')
            ->where('jenis_upload', 'M001-MPR')
            ->latest('id')
            ->value('kode_upload');

        $query->where('kode_upload', $lastBatch);
    }

    // Filter kode company
    if ($request->filled('kode_company')) {
        $query->where('kode_company', 'like', '%' . $request->kode_company . '%');
    }

    // Filter company name
    if ($request->filled('company_name')) {
        $query->where('company_name', 'like', '%' . $request->company_name . '%');
    }

    $data = $query
        ->orderBy('kode_company')
        ->get();
}
        // Reminder Innvoice //
        elseif ($request->jenis_upload == 'M002-Reminder Invoice') {

            $query = DB::table('reminder')
                ->select(
                    'kode_company',
                    'company_name',
                    DB::raw('COUNT(*) as total_invoice')
                )
                ->whereNull('sent_at');

            if ($request->filled('kode_upload')) {
                $query->where('kode_upload', $request->kode_upload);
            }

            if ($request->filled('kode_company')) {
                $query->where('kode_company', 'like', '%' . $request->kode_company . '%');
            }

            if ($request->filled('company_name')) {
                $query->where('company_name', 'like', '%' . $request->company_name . '%');
            }

            $data = $query
                ->groupBy('kode_company', 'company_name')
                ->orderBy('kode_company')
                ->get();
        }

        // ==========================
// Pembayaran
// ==========================
elseif ($request->jenis_upload == 'M003-Pembayaran') {

    // [DIUBAH] Tidak lagi pakai MIN(company_name)/GROUP BY SQL untuk company_name,
    // karena itu cuma ambil salah satu nama cabang apa adanya (masih ada nama kotanya).
    // Sekarang ambil semua baris dulu, lalu di PHP dicari "bagian nama yang sama"
    // dari semua cabang dengan kode_company yang sama (mis. "ELS COMPUTER PURWOKERTO",
    // "ELS COMPUTER SOLO", "ELS COMPUTER YOGYA" -> "ELS COMPUTER").
    $query = DB::table('pembayaran')
        ->select('kode_company', 'company_name', 'total')
        ->whereNull('sent_at');

    // FILTER BATCH
    if ($request->filled('kode_upload')) {

        $query->where(
            'kode_upload',
            $request->kode_upload
        );

    } else {

        $lastBatch = DB::table('upload_batches')
            ->where('jenis_upload', 'M003-Pembayaran')
            ->latest('id')
            ->value('kode_upload');

        $query->where('kode_upload', $lastBatch);
    }

    // FILTER KODE COMPANY
    if ($request->filled('kode_company')) {

        $query->where(
            'kode_company',
            'like',
            '%' . $request->kode_company . '%'
        );
    }

    // FILTER NAMA COMPANY
    if ($request->filled('company_name')) {

        $query->where(
            'company_name',
            'like',
            '%' . $request->company_name . '%'
        );
    }

    $rawRows = $query->get();

    // [DITAMBAHKAN] helper: cari bagian awal kata yang sama dari sekumpulan nama company
    $findCommonName = function (array $names) {

        if (count($names) === 1) {
            return $names[0];
        }

        // Pecah tiap nama jadi array kata, lalu bandingkan kata per kata
        $wordLists = array_map(fn($n) => preg_split('/\s+/', trim($n)), $names);
        $shortest  = min(array_map('count', $wordLists));

        $commonWords = [];

        for ($i = 0; $i < $shortest; $i++) {
            $word = $wordLists[0][$i];

            foreach ($wordLists as $words) {
                if (strcasecmp($words[$i], $word) !== 0) {
                    break 2; // kata ke-i sudah beda, berhenti
                }
            }

            $commonWords[] = $word;
        }

        // Kalau tidak ada kata yang sama sekali, fallback pakai nama pertama
        return $commonWords ? implode(' ', $commonWords) : $names[0];
    };

    $data = $rawRows
        ->groupBy('kode_company')
        ->map(function ($rows, $kodeCompany) use ($findCommonName) {

            $names = $rows->pluck('company_name')->unique()->values()->all();

            return (object) [
                'kode_company'      => $kodeCompany,
                'company_name'      => $findCommonName($names),
                'total_pembayaran'  => $rows->count(),
                'total_nominal'     => $rows->sum('total'),
            ];
        })
        ->sortBy('kode_company')
        ->values();
}

// [DITAMBAHKAN] PO Labor / APO1
elseif ($request->jenis_upload == 'M004-PO Labor') {

    $query = DB::table('apo')
        ->selectRaw('LEFT(case_id,3) as kode_company, company_name')
        ->whereNull('sent_at');

    if ($request->filled('kode_upload')) {
        $query->where('kode_upload', $request->kode_upload);
    }

    // [DIUBAH] Filter kode_company dipindah ke PHP (di bawah), setelah cabang
    // ELS COMPUTER digabung jadi satu kode, supaya filter cocok dengan kode yang tampil.
    if ($request->filled('company_name')) {
        $query->where('company_name', 'like', '%' . $request->company_name . '%');
    }

    $data = $query
        ->groupByRaw('LEFT(case_id,3), company_name')
        ->orderByRaw('LEFT(case_id,3)')
        ->get();

    // [DITAMBAHKAN] Gabungkan cabang yang namanya diawali "ELS COMPUTER" jadi
    // satu baris (kode 630). Case ID di data tidak berubah.
    $data = CompanyGroup::collapseApoListing($data);

    if ($request->filled('kode_company')) {
        $kodeFilter = (string) $request->kode_company;
        $data = $data->filter(fn ($r) => str_contains((string) $r->kode_company, $kodeFilter))->values();
    }
}

// [DITAMBAHKAN] KLA (masih nonaktif di BC Email, tapi query disiapkan)
elseif ($request->jenis_upload == 'D001-KLA') {

    $query = DB::table('kla')
        ->selectRaw('LEFT(case_id,1) as kode_company, company_name')
        ->whereNull('sent_at');

    if ($request->filled('kode_upload')) {
        $query->where('kode_upload', $request->kode_upload);
    }

    if ($request->filled('kode_company')) {
        $query->whereRaw('LEFT(case_id,3) LIKE ?', ['%' . $request->kode_company . '%']);
    }

    if ($request->filled('company_name')) {
        $query->where('company_name', 'like', '%' . $request->company_name . '%');
    }

    $data = $query
        ->groupByRaw('LEFT(case_id,1), company_name')
        ->orderByRaw('LEFT(case_id,1)')
        ->get();
}

        return view('bulk', compact(
            'data',
            'batches',
            'templates',
            'footers',
            'laborCategories' // [DITAMBAHKAN]

        ));
    }
}
