<?php

namespace App\Http\Controllers\Concerns\Import;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RawDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;

/**
 * Halaman data mentah, preview email, PN Master, filter/export prefix, batch & template.
 *
 * Dipecah dari ImportController supaya file lebih ringkas. Isi method TIDAK diubah.
 * Method: rawData(), previewEmail(), pnMaster(), filterPrefix(), exportPrefix(), getBatches(), deleteTemplate(), updateTemplate()
 */
trait HandlesImportDataPages
{
    // 🔥 DASHBOARD dipindah ke DashboardController.php (lihat method dashboard() di sana)

    // 🔥 RAW DATA (SUDAH JOIN)
    public function rawData()
    {

        $data = DB::table('raw_data')
            ->leftJoin('pn_master', DB::raw('TRIM(raw_data.case_id)'), '=', DB::raw('TRIM(pn_master.case_id)'))
            ->select(
                'raw_data.*',

                'pn_master.pn_code',
                'pn_master.part_request',
                'pn_master.so_no',
                'pn_master.eta_date',
                'pn_master.partin_date',

                DB::raw('DATEDIFF(NOW(), raw_data.received_date) as tat')
            )
            ->orderByDesc('raw_data.case_id')
            ->paginate(10);

        return view('raw_data', compact('data'));
    }

    public function previewEmail()
    {
        $data = DB::table('raw_data')
            ->leftJoin(
                'pn_master',
                DB::raw('TRIM(raw_data.case_id)'),
                '=',
                DB::raw('TRIM(pn_master.case_id)')
            )
            ->select(

                'raw_data.case_id',
                'raw_data.received_date',
                'raw_data.start_repair_date',
                'raw_data.company_name',
                'raw_data.case_status',
                'raw_data.company_city',
                'raw_data.product_no',
                'raw_data.product_name',
                'raw_data.part_in_date',

                'pn_master.pn_code',
                'pn_master.part_request',
                'pn_master.so_no',
                'pn_master.eta_date',
                'pn_master.part_in_date',

                DB::raw('DATEDIFF(NOW(), raw_data.received_date) as tat')

            )
            ->orderByDesc('raw_data.case_id')
            ->get();

        // 🔥 FILTER
        $fast = $data->where('tat', '<=', 5);

        $slow = $data->where('tat', '>', 5);

        return view(
            'email.report',
            compact('fast', 'slow')
        );
    }

    // 🔥 PN MASTER
    public function pnMaster()
    {
        $data = DB::table('pn_master')->paginate(10);
        return view('pn_master', compact('data'));
    }

    public function filterPrefix(Request $request)
    {
        $prefix = $request->prefix;

        $query = DB::table('raw_data')
            ->leftJoin('pn_master', 'raw_data.case_id', '=', 'pn_master.case_id');

        if ($prefix) {
            $query->where('raw_data.case_id', 'like', $prefix . '%');
        }

        $data = $query->select(
            'raw_data.case_id',
            'raw_data.received_date',
            'raw_data.start_repair_date',
            'raw_data.company_name',
            'raw_data.case_status',
            'raw_data.company_city',
            'raw_data.product_no',
            'raw_data.product_name',
            'raw_data.tat_case',


            'pn_master.pn_code',
            'pn_master.part_request',
            'pn_master.so_no',
            'pn_master.eta_date',
            'pn_master.partin_date'
        )
            ->orderByDesc('raw_data.case_id')
            ->paginate(10)
            ->appends(['prefix' => $prefix]);

        $total = $data->total(); // 🔥 ganti count()

        return view('raw_data', compact('data', 'total', 'prefix'));
    }

    public function exportPrefix(Request $request)
    {
        $prefix = $request->prefix;

        $data = DB::table('raw_data')
            ->leftJoin('pn_master', 'raw_data.case_id', '=', 'pn_master.case_id')
            ->where('raw_data.case_id', 'like', $prefix . '%')
            ->select(
                'raw_data.case_id',
                'raw_data.company_name',
                'raw_data.product_name',

                'pn_master.pn_code',
                'pn_master.part_request',
                'pn_master.so_no',
                'pn_master.eta_date',
                'pn_master.partin_date'
            )
            ->get();

        return Excel::download(new RawDataExport($data), 'export_' . $prefix . '.xlsx');
    }

    public function getBatches(Request $request)
    {
        $batch = DB::table('upload_batches')
            ->where('jenis_upload', $request->jenis_upload)
            ->latest('id')
            ->first();

        return response()->json(
            $batch ? [$batch] : []
        );
    }

    public function deleteTemplate($id)
    {
        \App\Models\EmailTemplate::find($id)->delete();

        return back()->with(
            'success',
            'Template berhasil dihapus'
        );
    }

    public function updateTemplate(Request $request, $id)
    {
        \App\Models\EmailTemplate::find($id)
            ->update([

                'nama_template' =>
                $request->nama_template,

                'subject' =>
                $request->subject,

                'kategori' =>
                $request->kategori,

                'body' =>
                $request->body

            ]);

        return back()->with(
            'success',
            'Template berhasil diupdate'
        );
    }
}
