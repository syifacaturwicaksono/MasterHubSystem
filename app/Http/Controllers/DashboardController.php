<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 🔥 DASHBOARD (DATA GABUNGAN)
    public function dashboard(\Illuminate\Http\Request $request)
    {
        // ★★★ DITAMBAHKAN: Dashboard per-akun. Tiap role cuma lihat jenis
        // monitoring miliknya sendiri. Admin bisa lihat semua + filter
        // "lihat sebagai" role tertentu lewat ?role_filter=apo2|apo1|fin1|all
        // ★★★ DIUBAH: samakan ke huruf kecil semua + buang spasi nyasar, biar gak masalah
        // kalau di database ke-simpen dengan huruf besar/kecil campur (mis. "Fin1", "APO2")
        // atau ada spasi tak sengaja (mis. "fin1 ")
        $userRole = trim(strtolower(auth()->user()->role));
        

        // Peta: role -> daftar jenis monitoring (key di $monitoringStats) yang boleh dilihat
        $roleMonitoringMap = [
            'apo2' => ['wip', 'pending14d', 'pending5d', 'kci', 'finishrepair', 'mpr'],
            'finance' => ['pembayaran', 'reminder',],
            'apo' => ['polabor'],
        ];

        if ($userRole === 'admin') {
            // Admin bisa pilih mau "lihat sebagai" siapa lewat dropdown filter
            $roleFilter = $request->get('role_filter', 'all');

            if ($roleFilter !== 'all' && isset($roleMonitoringMap[$roleFilter])) {
                $allowedMonitoring = $roleMonitoringMap[$roleFilter];
            } else {
                $roleFilter = 'all';
                $allowedMonitoring = array_merge(...array_values($roleMonitoringMap));
            }
        } else {
            $roleFilter = $userRole;
            $allowedMonitoring = $roleMonitoringMap[$userRole] ?? [];
        }

        // Prefix template_name di email_logs per role, dipakai buat filter
        // "Riwayat Email Terkirim", kartu ringkasan (stats), & "Statistik per Jenis
        // Monitoring" (WIP-dst diawali "W0" atau "M001", Pembayaran/Reminder
        // diawali "M002"/"M003", PO Labor diawali "M004")
        $templatePrefixMap = [
            'apo2' => ['W0', 'M001'],
            'finance' => ['M002', 'M003', 'Broadcast All (Kategori: Finance'],
            'apo' => ['M004'],
        ];

        // ★★★ DITAMBAHKAN: helper buat terapkan filter prefix template_name yang sama
        // ke query manapun, dipakai supaya kartu ringkasan (Total email terkirim, Company
        // unik, Total case) ikut kefilter sesuai role_filter, bukan cuma "Statistik per
        // Jenis Monitoring" & "Riwayat Email Terkirim".
        $applyRoleFilter = function ($query) use ($roleFilter, $templatePrefixMap) {
            if ($roleFilter !== 'all') {
                $prefixes = $templatePrefixMap[$roleFilter] ?? [];

                $query->where(function ($q) use ($prefixes) {
                    foreach ($prefixes as $prefix) {
                        $q->orWhere('template_name', 'like', $prefix . '%');
                    }
                });
            }

            return $query;
        };

        $dashboardQuery = $applyRoleFilter(
            DB::table('email_logs')
                ->select(
                    'kode_company',
                    'company_name',
                    'template_name',
                    'total_case',
                    'recipient',
                    'sent_at'
                )
        )->orderByDesc('sent_at');

        $dashboard = $dashboardQuery->paginate(10)->withQueryString();

        // ★★★ DIUBAH CLAUDE (BARU): rentang waktu buat breakdown bulan ini/bulan lalu ★★★
        $startOfThisMonth = now()->copy()->startOfMonth();
        $endOfThisMonth   = now()->copy()->endOfMonth();
        $startOfLastMonth = now()->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth   = now()->copy()->subMonthNoOverflow()->endOfMonth();

        // [DIUBAH] Setiap query di bawah dibungkus $applyRoleFilter(...) biar ikut kefilter
        $stats = [
            'total'    => $applyRoleFilter(DB::table('email_logs'))->count(),
            'hari_ini' => $applyRoleFilter(DB::table('email_logs'))->whereDate('sent_at', today())->count(),

            // ★★★ DIUBAH CLAUDE (BARU): Total email terkirim - breakdown bulan ini/bulan lalu
            'bulan_ini' => $applyRoleFilter(DB::table('email_logs'))
                ->whereBetween('sent_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
            'bulan_lalu' => $applyRoleFilter(DB::table('email_logs'))
                ->whereBetween('sent_at', [$startOfLastMonth, $endOfLastMonth])
                ->count(),

            'company'  => $applyRoleFilter(DB::table('email_logs'))->distinct('company_name')->count('company_name'),

            // ★★★ DIUBAH CLAUDE (BARU): Company unik - breakdown bulan ini/bulan lalu
            'company_bulan_ini' => $applyRoleFilter(DB::table('email_logs'))
                ->whereBetween('sent_at', [$startOfThisMonth, $endOfThisMonth])
                ->distinct('company_name')
                ->count('company_name'),
            'company_bulan_lalu' => $applyRoleFilter(DB::table('email_logs'))
                ->whereBetween('sent_at', [$startOfLastMonth, $endOfLastMonth])
                ->distinct('company_name')
                ->count('company_name'),

            'case'     => $applyRoleFilter(DB::table('email_logs'))->sum('total_case'),

            // ★★★ DIUBAH CLAUDE (BARU): Total case - breakdown hari ini/bulan ini/bulan lalu
            'case_hari_ini' => $applyRoleFilter(DB::table('email_logs'))
                ->whereDate('sent_at', today())
                ->sum('total_case'),
            'case_bulan_ini' => $applyRoleFilter(DB::table('email_logs'))
                ->whereBetween('sent_at', [$startOfThisMonth, $endOfThisMonth])
                ->sum('total_case'),
            'case_bulan_lalu' => $applyRoleFilter(DB::table('email_logs'))
                ->whereBetween('sent_at', [$startOfLastMonth, $endOfLastMonth])
                ->sum('total_case'),
        ];

        // ★★★ DIUBAH CLAUDE (BARU): statistik ringkas per jenis monitoring — Total Case (yang
        // masih aktif/belum terkirim, whereNull('sent_at') sama seperti di EmailController.php)
        // + jumlah case dengan aging tinggi + max aging. Ambang batas aging per jenis sama
        // persis dengan yang dipakai di EmailController.php (renderCaseTable). ★★★
        $monitoringStats = [

            'wip' => [
                'label'       => 'W001 - WIP',
                'total'       => DB::table('wip_datas')->whereNull('sent_at')->count(),
                'aging_alert' => DB::table('wip_datas')->whereNull('sent_at')->where('aging', '>', 14)->count(),
                'max_aging'   => DB::table('wip_datas')->whereNull('sent_at')->max('aging'),
            ],

            'pending14d' => [
                'label'       => 'W002 - Pending 14D',
                'total'       => DB::table('pending_14d')->whereNull('sent_at')->count(),
                'aging_alert' => DB::table('pending_14d')->whereNull('sent_at')->where('aging', '>=', 14)->count(),
                'max_aging'   => DB::table('pending_14d')->whereNull('sent_at')->max('aging'),
            ],

            'pending5d' => [
                'label'       => 'W003 - Pending 5D',
                'total'       => DB::table('pending')->whereNull('sent_at')->count(),
                'aging_alert' => DB::table('pending')->whereNull('sent_at')->where('aging', '>', 5)->count(),
                'max_aging'   => DB::table('pending')->whereNull('sent_at')->max('aging'),
            ],

            'kci' => [
                'label'       => 'W004 - KCI',
                'total'       => DB::table('kci')->whereNull('sent_at')->count(),
                'aging_alert' => DB::table('kci')->whereNull('sent_at')->where('aging', '>', 20)->count(),
                'max_aging'   => DB::table('kci')->whereNull('sent_at')->max('aging'),
            ],

            'finishrepair' => [
                'label'       => 'W005 - Finish Repair',
                'total'       => DB::table('finish_repair')->whereNull('sent_at')->count(),
                'aging_alert' => DB::table('finish_repair')->whereNull('sent_at')->where('aging', '>', 20)->count(),
                'max_aging'   => DB::table('finish_repair')->whereNull('sent_at')->max('aging'),
            ],

            // ★★★ DITAMBAHKAN: M001-MPR. Beda dari tipe lain, 1 baris = 1 company (bukan
            // 1 baris = 1 case), dan kolom numeriknya (total_case, latest_aging) disimpan
            // sebagai varchar format Indonesia (mis. "135,00"), jadi perlu di-CAST dulu
            // (REPLACE koma->titik) sebelum SUM/MAX/perbandingan angka. Threshold aging
            // tinggi (>= 14.5) disamakan dengan highlight merah "Latest Aging" di
            // EmailController::renderMprTable(). ★★★
            'mpr' => [
                'label' => 'M001 - MPR',
                'total' => (int) (DB::table('mpr_datas')
                    ->whereNull('sent_at')
                    ->selectRaw("SUM(CAST(REPLACE(total_case, ',', '.') AS DECIMAL(12,2))) as total")
                    ->value('total') ?? 0),
                'aging_alert' => DB::table('mpr_datas')
                    ->whereNull('sent_at')
                    ->whereRaw("CAST(REPLACE(latest_aging, ',', '.') AS DECIMAL(12,2)) >= 14.5")
                    ->count(),
                'max_aging' => DB::table('mpr_datas')
                    ->whereNull('sent_at')
                    ->selectRaw("MAX(CAST(REPLACE(latest_aging, ',', '.') AS DECIMAL(12,2))) as max_aging")
                    ->value('max_aging'),
            ],

            // ★★★ DITAMBAHKAN: M002-Reminder Invoice, M003-Pembayaran, M004-PO Labor.
            // Ketiga tabel ini tidak punya kolom 'aging', jadi aging_alert selalu 0 dan
            // max_aging '-' (di-handle otomatis oleh blade lewat `?? '-'`). 'total' di sini
            // = jumlah baris yang belum terkirim (whereNull('sent_at')), sama seperti
            // WIP/KCI/dst. ★★★
            'reminder' => [
                'label'       => 'M002 - Reminder Invoice',
                'total'       => DB::table('reminder')->whereNull('sent_at')->count(),
                'aging_alert' => 0,
                'max_aging'   => null,
            ],

            'pembayaran' => [
                'label'       => 'M003 - Pembayaran',
                'total'       => DB::table('pembayaran')->whereNull('sent_at')->count(),
                'aging_alert' => 0,
                'max_aging'   => null,
            ],

            'polabor' => [
                'label'       => 'M004 - PO Labor',
                'total'       => DB::table('apo')->whereNull('sent_at')->count(),
                'aging_alert' => 0,
                'max_aging'   => null,
            ],

        ];

        // ★★★ DITAMBAHKAN: persentase porsi tiap jenis monitoring terhadap total
        // gabungan semua jenis (dipakai untuk donut chart di dashboard). ★★★
        $grandTotalCase = array_sum(array_column($monitoringStats, 'total'));

        foreach ($monitoringStats as &$m) {
            $m['percent'] = $grandTotalCase > 0
                ? round(($m['total'] / $grandTotalCase) * 100, 1)
                : 0;
        }
        unset($m);

        // ★★★ DITAMBAHKAN: buang jenis monitoring yang bukan milik role ini
        $monitoringStats = array_intersect_key($monitoringStats, array_flip($allowedMonitoring));

        // ★★★ DITAMBAHKAN: History Upload Batch juga difilter sesuai role
        $uploadPrefixMap = [
            'apo2' => ['W0', 'M001'],
            'finance' => ['M002', 'M003'],
            'apo' => ['M004'],
        ];

        $uploadHistoryQuery = UploadBatch::query();

        if ($roleFilter !== 'all') {
            $prefixes = $uploadPrefixMap[$roleFilter] ?? [];

            $uploadHistoryQuery->where(function ($q) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $q->orWhere('jenis_upload', 'like', $prefix . '%');
                }
            });
        }

        $uploadHistory = $uploadHistoryQuery
    ->latest()
    ->paginate(10)
    ->withQueryString();

        return view('dashboard', compact(
            'dashboard',
            'stats',
            'monitoringStats',
            'uploadHistory',
            'userRole',      // [DITAMBAHKAN]
            'roleFilter'     // [DITAMBAHKAN]
        ));
    }
}