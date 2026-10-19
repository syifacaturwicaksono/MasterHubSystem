<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailMaster;
use App\Models\UserMaster;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | EMAIL MASTER
    |--------------------------------------------------------------------------
    */

    public function emailMaster(Request $request)
{
    $emails = EmailMaster::orderBy('kode_company', 'asc')->get();

    return view('email_master', compact('emails'));
}

/**
 * Menentukan kategori otomatis dari kode company.
 */
private function resolveKategoriFromKodeCompany(string $kodeCompany): string
{
    $kode = trim($kodeCompany);

    if ($kode === '') {
        return 'apo';
    }

    // Jika mengandung huruf → kla
    if (!ctype_digit($kode)) {
        return 'kla';
    }

    // Jika 4 digit → finance, selain itu → apo
    return strlen($kode) === 4 ? 'finance' : 'apo';
}

 public function storeEmailMaster(Request $request)
    {
        $request->validate([

            'kode_company' => 'required',
            'company_name' => 'required',
            'email' => 'required',
            'kategori' => 'required'

        ]);

        EmailMaster::create([

            'kode_company' => $request->kode_company,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'kategori' => $request->kategori

        ]);

        return redirect('/email-master')
            ->with('success', 'Email berhasil ditambahkan');
    }

public function updateEmailMaster(Request $request, $id)
{
    $data = EmailMaster::findOrFail($id);

    $data->update([
        'kode_company' => $request->kode_company,
        'company_name' => $request->company_name,
        'email' => $request->email,
        'kategori' => $this->resolveKategoriFromKodeCompany($request->kode_company),
    ]);

    // Kembalikan search & kategori sebelumnya
    return redirect('/email-master?search=' . urlencode($request->search) .
        '&kategori=' . urlencode($request->kategori))
        ->with('success', 'Email berhasil diupdate');
}

public function deleteEmailMaster(Request $request, $id)
{
    EmailMaster::findOrFail($id)->delete();

    // Kembalikan search & kategori sebelumnya
    return redirect('/email-master?search=' . urlencode($request->search) .
        '&kategori=' . urlencode($request->kategori))
        ->with('success', 'Email berhasil dihapus');
}


/**
 * Hapus banyak data berdasarkan checkbox yang dipilih
 */
public function deleteSelected(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:email_masters,id',
    ]);

    EmailMaster::whereIn('id', $request->ids)->delete();

    // Kembalikan search & kategori sebelumnya
    return redirect('/email-master?search=' . urlencode($request->search) .
        '&kategori=' . urlencode($request->kategori))
        ->with('success', 'Data terpilih berhasil dihapus');
}
    /*
|--------------------------------------------------------------------------
| footer MASTER
|--------------------------------------------------------------------------
*/
    public function footerMaster()
    {
        $footers = DB::table('footer_masters')
            ->orderBy('id', 'desc')
            ->get();

        return view(
            'footer_master',
            compact('footers')
        );
    }
    public function storeFooterMaster(Request $request)
    {
        DB::table('footer_masters')->insert([

            'footer_name' => $request->footer_name,

            'footer_html' => $request->footer_html,

            'created_at' => now(),

            'updated_at' => now()

        ]);

        return back()
            ->with('success', 'Footer berhasil ditambahkan');
    }
    public function updateFooterMaster(
        Request $request,
        $id
    ) {

        DB::table('footer_masters')
            ->where('id', $id)
            ->update([
                'footer_name' => $request->footer_name,
                'footer_html' => $request->footer_html,
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Footer berhasil diupdate'
        );
    }
    public function deleteFooterMaster($id)
    {
        DB::table('footer_masters')
            ->where('id', $id)
            ->delete();

        return back()
            ->with('success', 'Footer berhasil dihapus');
    }


    /*
    |--------------------------------------------------------------------------
    | USER MASTER
    |--------------------------------------------------------------------------
    */

    public function userMaster()
{
    // Role yang hanya boleh melihat akun sendiri
    $selfRoles = ['user', 'apo', 'apo2', 'finance'];

    if (in_array(auth()->user()->role, $selfRoles)) {

        $users = UserMaster::where('id', auth()->id())->get();

    } else {

        // admin & master melihat semua kecuali master
        $users = UserMaster::where('role', '!=', 'master')
            ->latest()
            ->get();

    }

    return view('user_master', compact('users'));
}
    public function storeUserMaster(Request $request)
    {
        $request->validate([

            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
            'password' => 'required'

        ]);

        UserMaster::create([

            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'smtp_password' => $request->smtp_password,
            'cc' => $request->cc

        ]);

        return redirect('/user-master')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function updateUserMaster(Request $request, $id)
{
    $user = UserMaster::findOrFail($id);

    $selfRoles = ['user', 'apo', 'apo2', 'finance'];

    // Role tertentu hanya boleh edit dirinya sendiri
    if (
        in_array(auth()->user()->role, $selfRoles) &&
        auth()->id() != $id
    ) {
        abort(403);
    }

    // Role tertentu hanya boleh ganti password
    if (in_array(auth()->user()->role, $selfRoles)) {

        if ($request->password) {

            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect('/user-master')
            ->with('success', 'Password berhasil diubah');
    }

    // ADMIN & MASTER
    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
        'smtp_password' => $request->smtp_password,
        'cc' => $request->cc
    ]);

    if ($request->password) {
        $user->update([
            'password' => Hash::make($request->password)
        ]);
    }

    return redirect('/user-master')
        ->with('success', 'User berhasil diupdate');
}
    public function deleteUserMaster($id)
    {
        UserMaster::findOrFail($id)->delete();

        return redirect('/user-master')
            ->with('success', 'User berhasil dihapus');
    }

}