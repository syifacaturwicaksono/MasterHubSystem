<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\ImportController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QueueSettingController;
use App\Http\Controllers\SparepartMasterController;


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {

    return view('auth.login');

})->name('login');

Route::post('/login', function (Request $request) {

    $credentials = $request->only(
        'name',
        'password'
    );

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        // [DIUBAH] Sapaan berbeda-beda setiap login (acak, tidak sama dengan
        // sapaan login sebelumnya). Tambah/ubah kalimat di daftar $sapaan;
        // {nama} diganti nama user, {waktu} diganti pagi/siang/sore/malam.
        // Nama panggilan per akun. Kunci = nama login (kolom name) atau role, huruf
        // kecil. Akun yang tidak ada di daftar ini disapa memakai nama loginnya.
        $namaPanggilan = [
            'finance' => 'Neysa Safa',
            'apo2'    => 'Shavira Aulia',
            'apo'     => 'Thony',
        ];

        $loginName = strtolower(trim((string) auth()->user()->name));
        $loginRole = strtolower(trim((string) (auth()->user()->role ?? '')));

        $nama = e(
            $namaPanggilan[$loginName]
            ?? $namaPanggilan[$loginRole]
            ?? auth()->user()->name
        );

        $jam = (int) now()->format('G');
        $waktu = match (true) {
            $jam >= 4 && $jam < 11  => 'pagi',
            $jam >= 11 && $jam < 15 => 'siang',
            $jam >= 15 && $jam < 18 => 'sore',
            default                 => 'malam',
        };

        $sapaan = [
            'Selamat datang, {nama}!',
            'Selamat {waktu}, {nama}!',
            'Halo {nama}, semangat bekerja hari ini!',
            'Senang melihatmu kembali, {nama}.',
            'Hai {nama}, siap menyelesaikan pekerjaan hari ini?',
            'Selamat {waktu}, {nama}. Semoga harimu lancar!',
            'Selamat datang kembali, {nama}!',
            'Yuk mulai bekerja, {nama}!',
        ];

        $cacheKey = 'sapaan_terakhir_' . auth()->id();
        $terakhir = \Illuminate\Support\Facades\Cache::get($cacheKey);

        $pilihan = array_keys($sapaan);

        if (count($pilihan) > 1 && $terakhir !== null) {
            $pilihan = array_values(array_diff($pilihan, [$terakhir]));
        }

        $index = $pilihan[array_rand($pilihan)];

        \Illuminate\Support\Facades\Cache::put($cacheKey, $index, now()->addDays(30));

        return redirect('/')->with(
            'success',
            str_replace(['{nama}', '{waktu}'], [$nama, $waktu], $sapaan[$index])
        );
    }

    return back()->with(
        'error',
        'Email atau password salah'
    );

});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', function () {

    Auth::logout();

    request()->session()->invalidate();

    request()->session()->regenerateToken();

    return redirect('/login')->with(
        'success',
        'Berhasil logout'
    );

});

/*
|--------------------------------------------------------------------------
| AUTH USER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | BULK EMAIL
    |--------------------------------------------------------------------------
    */

    Route::get('/bulk', [ImportController::class, 'bulk']);

    /*
    |--------------------------------------------------------------------------
    | EMAIL ACTION
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/email/preview',
        [EmailController::class, 'preview']
    )->name('email.preview');

    Route::post(
        '/email/send',
        [EmailController::class, 'send']
    )->name('email.send');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD
    |--------------------------------------------------------------------------
    */

    Route::get('/upload', [ImportController::class, 'uploadPage']);

    Route::post('/import', [ImportController::class, 'import']);

    /*
    |--------------------------------------------------------------------------
    | FILTER & EXPORT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/filter-prefix',
        [ImportController::class, 'filterPrefix']
    );

    Route::get(
        '/export-prefix',
        [ImportController::class, 'exportPrefix']
    );

    /*
    |--------------------------------------------------------------------------
    | RAW DATA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/raw-data',
        [ImportController::class, 'rawData']
    );

    /*
    |--------------------------------------------------------------------------
    | PN MASTER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pn-master',
        [ImportController::class, 'pnMaster']
    );

     /*
    |--------------------------------------------------------------------------
    | TEMPLATE MASTER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/template-master',
        [TemplateController::class, 'index']
    );

    Route::post(
        '/template-master/store',
        [TemplateController::class, 'store']
    );

    Route::post(
        '/template-master/update/{id}',
        [ImportController::class, 'updateTemplate']
    );

    Route::delete(
        '/template-master/delete/{id}',
        [ImportController::class, 'deleteTemplate']
    );

    /*
    |--------------------------------------------------------------------------
    | EMAIL MASTER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/email-master',
        [MasterController::class, 'emailMaster']
    );

    Route::post(
        '/email-master/store',
        [MasterController::class, 'storeEmailMaster']
    );

    Route::post(
        '/email-master/update/{id}',
        [MasterController::class, 'updateEmailMaster']
    );

    Route::delete(
        '/email-master/delete/{id}',
        [MasterController::class, 'deleteEmailMaster']
    );

    Route::delete('/email-master/delete-selected',
    [MasterController::class, 'deleteSelected']);

    // ==========================
// FOOTER MASTER
// ==========================

    Route::get(
        '/footer-master',
        [MasterController::class, 'footerMaster']
    );

    Route::post(
        '/footer-master/store',
        [MasterController::class, 'storeFooterMaster']
    );

    Route::put(
        '/footer-master/update/{id}',
        [MasterController::class, 'updateFooterMaster']
    );

    Route::delete(
        '/footer-master/delete/{id}',
        [MasterController::class, 'deleteFooterMaster']
    );

    /*
    |--------------------------------------------------------------------------
    | USER MASTER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/user-master',
        [MasterController::class, 'userMaster']
    );

    Route::post(
        '/user-master/store',
        [MasterController::class, 'storeUserMaster']
    );

    Route::post(
        '/user-master/update/{id}',
        [MasterController::class, 'updateUserMaster']
    );

    Route::delete(
        '/user-master/delete/{id}',
        [MasterController::class, 'deleteUserMaster']
    );
    

});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| TEST EMAIL
|--------------------------------------------------------------------------
*/

Route::get('/test-email', function () {

    Mail::raw(
        'SMTP berhasil digunakan',
        function ($message) {

            $message->to(
                'ce4.semarang@harmoniputra.com'
            )->subject(
                    'Test SMTP Laravel'
                );

        }
    );

    return 'Email berhasil dikirim';

});

Route::put(
    '/template-master/update/{id}',
    [TemplateController::class, 'update']
);

Route::get('/get-batches', [ImportController::class, 'getBatches']);

Route::post('/apo/update-labor', [ImportController::class, 'updateApoLabor'])
    ->name('apo.updateLabor');

/*
|--------------------------------------------------------------------------
| broadcast all
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/broadcast-all', [BroadcastController::class, 'index']);
    Route::post('/broadcast-all/preview', [BroadcastController::class, 'preview']);
    Route::post('/broadcast-all/send', [BroadcastController::class, 'send']);
});

/*
|--------------------------------------------------------------------------
| queue setting time
|--------------------------------------------------------------------------
*/

// [DIUBAH] dibungkus middleware auth supaya halaman setting timer tidak bisa dibuka tanpa login
Route::middleware('auth')->group(function () {
    Route::get('/queue-setting', [QueueSettingController::class, 'edit'])->name('queue-setting.edit');
    Route::post('/queue-setting', [QueueSettingController::class, 'update'])->name('queue-setting.update');
    Route::post('/queue-setting/reset', [QueueSettingController::class, 'resetQueue'])
        ->name('queue-setting.reset');

    // [DITAMBAHKAN] simulasi jadwal pengiriman (dihitung script Python)
    Route::post('/queue-setting/preview', [QueueSettingController::class, 'preview'])
        ->name('queue-setting.preview');
});
    

Route::post('/upload/resolve-parts', [ImportController::class, 'resolvePartsLabor'])
    ->name('upload.resolveParts');

Route::post('/upload/save-part-classification', [ImportController::class, 'savePartClassification'])
    ->name('upload.savePartClassification');

/*
|--------------------------------------------------------------------------
| RR (Re Repair) - PO Labor
|--------------------------------------------------------------------------
| [DITAMBAHKAN] Tombol "Next" di popup Review Part & Kategori (PO Labor)
| sekarang mengarah ke sini dulu, bukan langsung commit ke tabel apo.
| upload.rr   : terima file + override (case/labor/keterangan), simpan
|               file-nya sementara, deteksi RR (Serial No sama, Case ID
|               beda dalam 1 file), tampilkan halaman RR.
| upload.rrConfirm : dipanggil dari tombol Export/Simpan/Simpan & Export
|               di halaman RR -> baru di sini commit sungguhan ke DB,
|               pakai file yang sudah distash tadi.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| REVISI PO LABOR
|--------------------------------------------------------------------------
| [DITAMBAHKAN] Buka lagi data PO Labor terakhir di database (belum terkirim)
| di popup review yang sama, lalu simpan revisinya ke baris apo yang ada.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/upload/revisi/data', [ImportController::class, 'revisiPoLaborData'])
        ->name('upload.revisiData');

    Route::post('/upload/revisi/save', [ImportController::class, 'revisiPoLaborSave'])
        ->name('upload.revisiSave');
});

// [DITAMBAHKAN] RR lintas bulan: cek case lama (riwayat 90 hari) & isi riwayat
Route::middleware(['auth'])->group(function () {
    Route::post('/upload/rr-history', [ImportController::class, 'rrHistoryCheck'])
        ->name('upload.rrHistoryCheck');

    Route::post('/upload/history', [ImportController::class, 'rrHistoryUpload'])
        ->name('upload.historyUpload');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/upload/rr', [ImportController::class, 'rrPreview'])
        ->name('upload.rr');

    Route::post('/upload/rr/confirm', [ImportController::class, 'rrConfirm'])
        ->name('upload.rrConfirm');
});

/*
|--------------------------------------------------------------------------
| sparepart master
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/sparepart-master', [SparepartMasterController::class, 'index'])->name('sparepart-master.index');
    Route::post('/sparepart-master/preview', [SparepartMasterController::class, 'preview'])->name('sparepart-master.preview');
    Route::post('/sparepart-master/store', [SparepartMasterController::class, 'store'])->name('sparepart-master.store');
    Route::post('/sparepart-master/update/{id}', [SparepartMasterController::class, 'update'])->name('sparepart-master.update');
});