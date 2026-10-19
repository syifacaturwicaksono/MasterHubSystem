<?php

namespace App\Http\Controllers\Concerns\Import;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SparepartMasterController;

/**
 * PO Labor: update APO, klasifikasi part, sinkron Sparepart Master, dan revisi data.
 *
 * Dipecah dari ImportController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: updateApoLabor(), resolvePartsLabor(), savePartClassification(), syncPartToSparepartMaster(), revisiPoLaborData(), revisiPoLaborSave()
 */
trait HandlesPoLaborRevision
{
    // [DITAMBAHKAN] Dipanggil dari modal PO Labor di BC Email — user koreksi
    // kategori 1 case secara manual, Labor otomatis dihitung ulang sesuai
    // kategori baru + Product Tower case itu. Sekalian "hafalan" part_master
    // ikut diperbaiki (kalau case ini punya part_no), supaya part yang sama
    // gak salah kategori lagi di upload berikutnya.
    public function updateApoLabor(Request $request)
    {
        $request->validate([
            'id'          => 'required|integer',
            'category_id' => 'required|integer',
        ]);

        $apoRow = DB::table('apo')->where('id', $request->id)->first();

        if (!$apoRow) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $category = DB::table('labor_categories')->find($request->category_id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        $priceColumn = strtoupper(trim($apoRow->product_tower ?? '')) === 'IPG' ? 'ipg_price' : 'psg_price';
        $newLabor = $category->{$priceColumn};

        DB::table('apo')->where('id', $request->id)->update([
            'part_category' => $category->name,
            'labor'         => $newLabor,
            'updated_at'    => now(),
        ]);

        // Perbaiki juga hafalan di part_master, kalau case ini punya part_no
        if (!empty($apoRow->part_no)) {
            \App\Services\PartCategoryResolver::saveClassification(
                $apoRow->part_no,
                $apoRow->part_name,
                (int) $request->category_id
            );
        }

        return response()->json([
            'success'  => true,
            'labor'    => $newLabor,
            'category' => $category->name,
        ]);
    }

    // [DIUBAH] Dipanggil dari Upload Sheet (PO Labor) via AJAX — terima daftar
    // part_no + part_name + product_tower, kembalikan status "known" (sudah ada
    // di part_master, dipakai kategori & harganya) atau "unknown" (belum pernah
    // ada — dikasih SARAN tebakan kategori buat pre-fill dropdown popup, TAPI
    // tidak di-insert ke database sampai user konfirmasi manual).
    public function resolvePartsLabor(Request $request)
    {
        $parts = $request->input('parts', []);

        $result = [];

        foreach ($parts as $part) {

            $partNo = trim((string) ($part['part_no'] ?? ''));

            if ($partNo === '' || isset($result[$partNo])) {
                continue;
            }

            $partName = $part['part_name'] ?? null;

            $resolved = \App\Services\PartCategoryResolver::resolve(
                $partNo,
                $partName,
                $part['product_tower'] ?? '',
                $part['product_name'] ?? ''
            );

            $result[$partNo] = [
                'known'            => $resolved['known'],
                'category'         => $resolved['category_name'],
                'price'            => $resolved['price'],
                // [DITAMBAHKAN] cuma diisi kalau belum dikenal — saran buat pre-fill dropdown
                'guessed_category' => $resolved['known']
                    ? null
                    : \App\Services\PartCategoryResolver::guessCategory($partName ?? ''),
            ];
        }

        return response()->json($result);
    }

    // [DITAMBAHKAN] Dipanggil dari popup "Klasifikasi Part Baru" setelah user
    // pilih & konfirmasi kategori manual untuk part yang belum dikenal.
    public function savePartClassification(Request $request)
    {
        $classifications = $request->input('classifications', []);

        foreach ($classifications as $item) {

            $partNo = trim((string) ($item['part_no'] ?? ''));
            $categoryId = $item['category_id'] ?? null;

            if ($partNo === '' || !$categoryId) {
                continue;
            }

            \App\Services\PartCategoryResolver::saveClassification(
                $partNo,
                $item['part_name'] ?? null,
                (int) $categoryId
            );

            // [DITAMBAHKAN] Sinkron ke Sparepart Master - part baru yang
            // baru saja dikonfirmasi kategorinya di popup Review Part &
            // Kategori (PO Labor) otomatis masuk juga ke tabel
            // sparepart_master, jadi langsung muncul di halaman/tabel
            // Sparepart Master (database maupun web), tanpa perlu upload
            // ulang manual lewat menu Sparepart Master.
            $this->syncPartToSparepartMaster(
                $partNo,
                $item['part_name'] ?? null,
                (int) $categoryId,
                (string) ($item['product_tower'] ?? '')
            );
        }

        return response()->json(['success' => true]);
    }

    // [DITAMBAHKAN] Insert part ke sparepart_master kalau belum ada di sana.
    // ------------------------------------------------------------
    // Dipanggil dari savePartClassification() di atas. Kategori & harga
    // ikut aturan yang sama dengan SparepartMasterController: harga
    // SELALU dihitung ulang di server dari labor_categories berdasarkan
    // kategori + divisi (IPG/PSG), bukan dikirim dari JavaScript.
    //
    // Kalau part_number-nya TERNYATA sudah ada di sparepart_master
    // (misal sudah pernah diupload/diedit manual lewat menu Sparepart
    // Master), data yang sudah ada TIDAK ditimpa - sama seperti perilaku
    // SparepartMasterController::store() yang skip part yang sudah ada.
    // ------------------------------------------------------------
    private function syncPartToSparepartMaster(
        string $partNo,
        ?string $partName,
        int $categoryId,
        string $productTower
    ): void {
        if ($partNo === '') {
            return;
        }

        $alreadyExists = DB::table('sparepart_master')
            ->where('part_number', $partNo)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        $category = DB::table('labor_categories')
            ->where('id', $categoryId)
            ->first();

        if (!$category) {
            return;
        }

        $divisiUpper = strtoupper(trim($productTower));

        $divisi = in_array($divisiUpper, ['IPG', 'PSG'], true)
            ? $divisiUpper
            : null;

        $harga = null;

        if ($divisi === 'PSG') {
            $harga = $category->psg_price;
        } elseif ($divisi === 'IPG') {
            $harga = $category->ipg_price;
        }

        DB::table('sparepart_master')->insert([
            'part_number' => $partNo,
            'deskripsi'   => $partName,
            'kategori'    => $category->name,
            'divisi'      => $divisi,
            'harga'       => $harga,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    // =========================================================
    // [DITAMBAHKAN] 🔥 REVISI PO LABOR
    // -----------------------------------------------------------------
    // Buka lagi data PO Labor TERAKHIR di tabel apo (yang belum terkirim /
    // sent_at kosong) di popup review yang sama (RR -> Review Part),
    // tanpa upload & validasi ulang dari awal. Simpan revisi memperbarui
    // baris apo yang ada (tidak truncate, tidak membuat baris baru).
    // =========================================================
    public function revisiPoLaborData(Request $request)
    {
        $kode = trim((string) $request->query('kode_upload', ''));

        if ($kode === '') {
            $kode = (string) DB::table('apo')
                ->where('jenis_monitoring', 'M004-PO Labor')
                ->whereNull('sent_at')
                ->orderByDesc('id')
                ->value('kode_upload');
        }

        if ($kode === '') {
            return response()->json([
                'message' => 'Belum ada data PO Labor yang belum terkirim untuk direvisi.',
            ], 404);
        }

        // Semua baris batch ini (terkirim & belum) dipakai untuk menentukan RR,
        // supaya urutan "case pertama per Serial No" sama seperti saat upload.
        $all = DB::table('apo')
            ->where('jenis_monitoring', 'M004-PO Labor')
            ->where('kode_upload', $kode)
            ->orderBy('id')
            ->get();

        $bySerial = [];

        foreach ($all as $r) {

            $serial = trim((string) $r->serial_no);
            $caseId = trim((string) $r->case_id);

            if ($serial === '' || $caseId === '') {
                continue;
            }

            if (!isset($bySerial[$serial])) {
                $bySerial[$serial] = [];
            }

            if (isset($bySerial[$serial][$caseId])) {
                continue;
            }

            $bySerial[$serial][$caseId] = [
                'case_id'         => $caseId,
                'company_name'    => (string) $r->company_name,
                'product_tower'   => (string) $r->product_tower,
                'product_name'    => (string) $r->product_name,
                'case_status'     => (string) $r->case_status,
                'warranty_status' => (string) $r->warranty_status,
                'repair_action'   => (string) $r->repair_action,
                'sent'            => $r->sent_at !== null,
            ];
        }

        // [DITAMBAHKAN] Case lama dari apo_history (lintas bulan, <= RR_WINDOW_DAYS)
        $historyInput = [];

        foreach ($all as $r) {
            $cid = trim((string) $r->case_id);
            $sn  = trim((string) $r->serial_no);

            if ($cid === '' || $sn === '' || isset($historyInput[$cid])) {
                continue;
            }

            $historyInput[$cid] = [
                'case_id'       => $cid,
                'serial_no'     => $sn,
                'received_date' => $this->parseExcelDate($r->received_date),
            ];
        }

        $histMatches  = $this->findRrHistoryMatches(array_values($historyInput));
        $histBySerial = [];

        foreach ($histMatches as $cid => $matched) {
            foreach ($matched as $m) {
                $sn = $historyInput[$cid]['serial_no'];
                $existing = $histBySerial[$sn][$m['case_id']] ?? null;

                // satu case lama bisa cocok ke beberapa case baru: tampilkan selisih terkecil
                if ($existing === null || $m['days'] < $existing['days']) {
                    $histBySerial[$sn][$m['case_id']] = $m;
                }
            }
        }

        $rrCaseIds = [];
        $rrGroups  = [];

        foreach ($bySerial as $serial => $cases) {

            $histCases = array_values($histBySerial[$serial] ?? []);

            if (count($cases) < 2 && empty($histCases)) {
                continue;
            }

            $list = array_values($cases);

            foreach (array_slice($list, 1) as $c) {
                $rrCaseIds[$c['case_id']] = true;
            }

            // case yang punya case lama di riwayat juga RR
            foreach ($list as $c) {
                if (isset($histMatches[$c['case_id']])) {
                    $rrCaseIds[$c['case_id']] = true;
                }
            }

            // Grup ditampilkan hanya kalau masih ada case yang belum terkirim
            $hasUnsent = false;

            foreach ($list as $c) {
                if (!$c['sent']) {
                    $hasUnsent = true;
                    break;
                }
            }

            if ($hasUnsent) {
                // case lama (riwayat) ditaruh paling atas sebagai referensi
                $historyList = array_map(function ($h) {
                    $h['sent'] = true;
                    return $h;
                }, $histCases);

                $rrGroups[] = [
                    'serial_no' => (string) $serial,
                    'cases'     => array_merge($historyList, $list),
                ];
            }
        }

        $rows = [];

        foreach ($all as $r) {

            if ($r->sent_at !== null) {
                continue;
            }

            // [DITAMBAHKAN] Baris tanpa Case ID tidak bisa direvisi (kunci update
            // adalah Case ID) -> jangan ditampilkan di popup revisi.
            if (trim((string) $r->case_id) === '') {
                continue;
            }

            $row = (array) $r;
            $row['is_rr'] = isset($rrCaseIds[trim((string) $r->case_id)]);

            $rows[] = $row;
        }

        return response()->json([
            'kode_upload' => $kode,
            'total_unsent' => count($rows),
            'total_sent'   => $all->count() - count($rows),
            'rows'         => $rows,
            'rr_groups'    => $rrGroups,
        ]);
    }

    public function revisiPoLaborSave(Request $request)
    {
        $request->validate([
            'kode_upload'    => 'required|string',
            'rows'           => 'required|array|min:1',
            // [DIUBAH] tidak lagi 'required' per baris: baris tanpa Case ID
            // cukup dilewati di bawah (tidak menggagalkan seluruh simpan revisi).
            'rows.*.case_id' => 'nullable',
        ]);

        $kode = $request->input('kode_upload');

        // Hanya baris yang belum terkirim yang boleh direvisi.
        $existing = DB::table('apo')
            ->where('jenis_monitoring', 'M004-PO Labor')
            ->where('kode_upload', $kode)
            ->whereNull('sent_at')
            ->get()
            ->keyBy(fn ($r) => trim((string) $r->case_id));

        $updated = 0;

        DB::transaction(function () use ($request, $existing, &$updated) {

            foreach ($request->input('rows', []) as $p) {

                $caseId = trim((string) ($p['case_id'] ?? ''));
                $row    = $existing->get($caseId);

                if (!$row) {
                    continue;
                }

                $partNo   = trim((string) ($p['part_no'] ?? ''));
                $partName = trim((string) ($p['part_name'] ?? ''));
                $category = trim((string) ($p['category'] ?? ''));
                $manual   = trim((string) ($p['keterangan'] ?? ''));
                $isRr     = !empty($p['is_rr']);

                $labor = (isset($p['labor']) && $p['labor'] !== '' && $p['labor'] !== null)
                    ? (float) $p['labor']
                    : null;

                $caseStatus = strtolower((string) $row->case_status);

                // --- aturan yang sama dengan ApoImport ---------------------

                // Penanda kategori tanpa Labor (Onsite CISS (no part) / SARC)
                $noLaborCategory = null;

                if (preg_match('/\[\[CAT:([^\]]+)\]\]/', $manual, $m)) {

                    foreach (['Onsite CISS (no part)', 'SARC'] as $allowed) {
                        if (strcasecmp(trim($m[1]), $allowed) === 0) {
                            $noLaborCategory = $allowed;
                            break;
                        }
                    }

                    $manual = trim(preg_replace('/\[\[CAT:[^\]]+\]\]/', '', $manual));
                }

                if (preg_match('/\bvoid case\b/i', $manual)) {
                    $labor    = null;
                    $category = 'Void Case';
                }

                if ($noLaborCategory !== null) {
                    $labor    = null;
                    $category = $noLaborCategory;
                }

                // Cancel selalu tanpa Labor
                if (str_contains($caseStatus, 'cancel')) {
                    $labor = null;
                }

                // RR tanpa Labor, kecuali dipilih "Dapat Labor"
                if ($isRr && !preg_match('/dapat labor/i', $manual)) {
                    $labor = null;
                }

                $isCloseRepairNoPart = $partNo === ''
                    && str_contains($caseStatus, 'close repair');

                if ($isRr) {

                    if ($manual === '') {
                        $keterangan = 'RR';
                    } elseif (preg_match('/\bRR\b/i', $manual)) {
                        $keterangan = $manual;
                    } else {
                        $keterangan = 'RR - ' . $manual;
                    }

                } elseif ($isCloseRepairNoPart) {

                    $keterangan = $manual === ''
                        ? 'Close Repair tanpa part'
                        : 'Close Repair tanpa part - ' . $manual;

                } elseif ($manual !== '') {

                    $keterangan = $manual;

                } else {

                    $keterangan = $row->repair_action;
                }

                DB::table('apo')
                    ->where('id', $row->id)
                    ->update([
                        'part_no'       => $partNo !== '' ? $partNo : null,
                        'part_name'     => $partName !== '' ? $partName : null,
                        'part_category' => $category !== '' ? $category : null,
                        'labor'         => $labor,
                        'keterangan'    => $keterangan,
                        'updated_at'    => now(),
                    ]);

                $updated++;
            }
        });

        return response()->json([
            'message' => "Revisi tersimpan: {$updated} case diperbarui di BC Email.",
            'updated' => $updated,
        ]);
    }
}
