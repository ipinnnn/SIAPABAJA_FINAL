<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;

// Controllers
use App\Http\Controllers\Unit\UnitController;
use App\Http\Controllers\PPK\PPKController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\Chatbot\ChatbotController;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/

Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])
    ->middleware('auth')
    ->name('chatbot.ask');

Route::post('/chatbot/public', [ChatbotController::class, 'askPublic'])
    ->name('chatbot.public');

Route::get('/chatbot/health', [ChatbotController::class, 'health'])
    ->name('chatbot.health');

Route::view('/', 'Landing.Index')->name('landing');


// Login (GET form)
Route::get('/login', function () {
    return view('Auth.login');
})->name('login');

// Login (POST proses)
Route::post('/login', function (Request $request) {

    $request->validate([
        'email'                 => ['required', 'email'],
        'password'              => ['required'],
        'g-recaptcha-response'  => ['required'],
    ], [
        'g-recaptcha-response.required' => 'Harap verifikasi captcha terlebih dahulu.',
    ]);

    $captchaResponse = $request->input('g-recaptcha-response');

    $verify = Http::asForm()->post(
        'https://www.google.com/recaptcha/api/siteverify',
        [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $captchaResponse,
            'remoteip' => $request->ip(),
        ]
    )->json();

    if (!($verify['success'] ?? false)) {
        return back()
            ->withErrors(['g-recaptcha-response' => 'Captcha tidak valid. Silakan coba lagi.'])
            ->withInput($request->only('email'));
    }

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->boolean('remember'))) {

        $request->session()->regenerate();

        $user = Auth::user();

        // 🔥 TAMBAHAN: CEK STATUS USER
        if ($user->status !== 'active') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif. Hubungi Super Admin.'
            ]);
        }

        return redirect()->route('home');
    }

    return back()
        ->withErrors(['email' => 'Email atau kata sandi salah.'])
        ->withInput($request->only('email'));
})->name('login.post');

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('landing');
})->name('logout');

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('landing');
})->name('logout.get');

// Homepage
Route::view('/home', 'Home.index')->name('home');
Route::view('/home-preview', 'Home.index')->name('home.preview');

// Arsip
Route::view('/ArsipPBJ', 'Landing.pbj')->name('ArsipPBJ');
Route::redirect('/landing/ArsipPBJ', '/ArsipPBJ')->name('landing.pbj');

Route::get('/landing/chartstats', function (Request $request) {

    $tahun  = $request->query('tahun');
    $unitId = $request->query('unit_id');

    // ✅ FIX: hanya loloskan jika benar-benar angka valid
    $tahun  = (is_numeric($tahun)  && (int)$tahun  > 0) ? (int)$tahun  : null;
    $unitId = (is_numeric($unitId) && (int)$unitId > 0) ? (int)$unitId : null;

    $base = \App\Models\Pengadaan::query()
        ->where('status_arsip', 'Publik');

    if ($tahun)  $base->where('tahun',   $tahun);
    if ($unitId) $base->where('unit_id', $unitId);

    // STATUS
    $statusLabels = [
        'Perencanaan',
        'Pemilihan',
        'Pelaksanaan',
        'Serah Terima',
        'Selesai',
    ];

    $statusCounts = (clone $base)
        ->selectRaw('status_pekerjaan as status, COUNT(*) as total')
        ->groupBy('status_pekerjaan')
        ->pluck('total', 'status')
        ->toArray();

    $statusValues = array_map(
        fn($label) => (int) ($statusCounts[$label] ?? 0),
        $statusLabels
    );

    // METODE
    $methodLabels = [
        'Tender Terbuka',
        'E-Purchasing / E-Catalog',
        'Pengadaan Langsung',
        'Penunjukan Langsung',
        'Tender Terbatas',
        'Swakelola',
    ];

    $bucket = array_fill_keys($methodLabels, 0);

    $methodCounts = (clone $base)
        ->selectRaw('metode_pengadaan as metode, COUNT(*) as total')
        ->groupBy('metode_pengadaan')
        ->pluck('total', 'metode')
        ->toArray();

    foreach ($methodCounts as $metode => $total) {
        $m = strtolower(trim((string) $metode));
        if (str_contains($m, 'tender terbuka'))          $bucket['Tender Terbuka']           += (int) $total;
        elseif (str_contains($m, 'e-purchasing') ||
                str_contains($m, 'e catalog')   ||
                str_contains($m, 'e-catalog'))            $bucket['E-Purchasing / E-Catalog'] += (int) $total;
        elseif (str_contains($m, 'pengadaan langsung'))  $bucket['Pengadaan Langsung']        += (int) $total;
        elseif (str_contains($m, 'penunjukan langsung')) $bucket['Penunjukan Langsung']       += (int) $total;
        elseif (str_contains($m, 'tender terbatas'))     $bucket['Tender Terbatas']           += (int) $total;
        elseif (str_contains($m, 'swakelola'))           $bucket['Swakelola']                 += (int) $total;
    }

    return response()->json([
        'status' => [
            'labels' => $statusLabels,
            'values' => $statusValues,
        ],
        'metode' => [
            'labels' => array_keys($bucket),
            'values' => array_values($bucket),
        ],
        'donut' => $statusValues,
        'bar'   => array_values($bucket),
    ]);
})->name('landing.chartstats');

Route::view('/home/ArsipPBJ', 'Home.pbj')->name('home.pbj');

Route::redirect('/home/arsippbj', '/home/ArsipPBJ')->name('home.arsippbj');
Route::redirect('/home/arsip-pbj', '/home/ArsipPBJ');

Route::get('/arsip/{id}', function ($id) {
    return view('Landing.LihatDetail', compact('id'));
})->name('arsip.detail');

Route::get('/file-viewer', [UnitController::class, 'fileViewer'])
    ->name('file.viewer');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/home/dashboard', function () {

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = strtolower(trim((string) $user->role));

        if ($role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }

        if (in_array($role, ['ppk', 'ppk utama', 'ppk_utama'], true)) {
            return redirect()->route('ppk.dashboard');
        }

        if (in_array($role, ['unit', 'admin unit', 'admin_unit'], true)) {
            return redirect()->route('unit.dashboard');
        }

        return redirect()->route('home');
    })->name('home.dashboard');

    /*
    |--------------------------------------------------------------------------
    | UNIT
    |--------------------------------------------------------------------------
    */
    Route::prefix('unit')->name('unit.')->group(function () {
        Route::get('/dashboard', [UnitController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/stats', [UnitController::class, 'dashboardStats'])->name('dashboard.stats');
        Route::get('/dashboard/data', [UnitController::class, 'dashboardStats'])->name('dashboard.data');

        Route::get('/arsip', [UnitController::class, 'arsipIndex'])->name('arsip');
        Route::get('/arsippbj', [UnitController::class, 'arsipIndex'])->name('arsippbj');
        Route::get('/arsip-pbj', [UnitController::class, 'arsipIndex'])->name('arsip.pbj');

        Route::get('/arsip/export', [UnitController::class, 'arsipExport'])->name('arsip.export');

        Route::get(
            '/histori-aktivitas',
            [UnitController::class, 'historiAktivitas']
        )->name('histori');


        Route::get('/arsip/{id}/edit', [UnitController::class, 'arsipEdit'])->name('arsip.edit');
        Route::put('/arsip/{id}', [UnitController::class, 'arsipUpdate'])->name('arsip.update');

        Route::delete('/arsip', [UnitController::class, 'arsipBulkDestroy'])->name('arsip.bulkDestroy');
        Route::delete('/arsip/{id}', [UnitController::class, 'arsipDestroy'])->name('arsip.destroy');

        Route::get('/pengadaan/tambah', [UnitController::class, 'pengadaanCreate'])->name('pengadaan.create');
        Route::post('/pengadaan/store', [UnitController::class, 'pengadaanStore'])->name('pengadaan.store');

        Route::get('/arsip/{id}/dokumen/{field}/{file}', [UnitController::class, 'showDokumen'])
            ->where(['field' => '[A-Za-z0-9_\-]+', 'file' => '.+'])
            ->name('arsip.dokumen.show');

        Route::delete('/arsip/{id}/dokumen', [UnitController::class, 'hapusDokumenFile'])
            ->name('arsip.dokumen.hapus');

        Route::get('/arsip/{id}/dokumen-download', [UnitController::class, 'downloadDokumen'])
            ->name('arsip.dokumen.download');

        Route::get('/kelola-akun', [UnitController::class, 'kelolaAkun'])->name('kelola.akun');
        Route::put('/akun', [UnitController::class, 'updateAkun'])->name('akun.update');
    });

    /*
    |--------------------------------------------------------------------------
    | PPK
    |--------------------------------------------------------------------------
    */
    Route::prefix('ppk')
        ->name('ppk.')
        ->middleware('role:ppk')
        ->group(function () {

            Route::get('/dashboard', [PPKController::class, 'dashboard'])->name('dashboard');
            Route::get('/dashboard/data', [PPKController::class, 'dashboardData'])->name('dashboard.data');

            Route::get('/arsip', [PPKController::class, 'arsipIndex'])->name('arsip');

            Route::get('/arsip/{id}/edit', [PPKController::class, 'arsipEdit'])->name('arsip.edit');
            Route::put('/arsip/{id}', [PPKController::class, 'arsipUpdate'])->name('arsip.update');

            Route::delete('/arsip/{id}/delete', [PPKController::class, 'arsipDelete'])->name('arsip.delete');

            Route::get('/pengadaan/tambah', [PPKController::class, 'pengadaanCreate'])->name('pengadaan.create');
            Route::post('/pengadaan/store', [PPKController::class, 'pengadaanStore'])->name('pengadaan.store');

            Route::get('/arsip/{id}/dokumen/{field}/{file}', [PPKController::class, 'showDokumen'])
                ->where(['field' => '[A-Za-z0-9_\-]+', 'file' => '.+'])
                ->name('arsip.dokumen.show');
            Route::get('/arsip/{id}/dokumen-download', [PPKController::class, 'downloadDokumen'])
                ->name('arsip.dokumen.download');

            Route::get('/kelola-akun', [PPKController::class, 'kelolaAkun'])->name('kelola.akun');
            Route::put('/akun', [PPKController::class, 'updateAkun'])->name('akun.update');

            Route::get('/histori', [PPKController::class, 'historiAktivitas'])->name('histori');
        });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN (FIXED ONLY)
    |--------------------------------------------------------------------------
    */
    Route::prefix('super-admin')
        ->name('superadmin.')
        ->middleware('role:super_admin') // ✅ FIX
        ->group(function () {

            Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/dashboard/data', [SuperAdminController::class, 'dashboardData'])->name('dashboard.data');

            Route::get('/arsip', [SuperAdminController::class, 'arsipIndex'])->name('arsip');

            Route::get('/arsip/{id}/edit', [SuperAdminController::class, 'arsipEdit'])->name('arsip.edit');
            Route::put('/arsip/{id}', [SuperAdminController::class, 'arsipUpdate'])->name('arsip.update');

            Route::delete('/arsip/{id}/delete', [SuperAdminController::class, 'arsipDelete'])->name('arsip.delete');

            Route::get('/pengadaan/tambah', [SuperAdminController::class, 'pengadaanCreate'])->name('pengadaan.create');
            Route::post('/pengadaan/store', [SuperAdminController::class, 'pengadaanStore'])->name('pengadaan.store');

            Route::get(
                '/arsip/{id}/dokumen/{field}/{file}',
                [SuperAdminController::class, 'showDokumen']
            )->where([
                'field' => '[A-Za-z0-9_\-]+',
                'file'  => '.+'
            ])->name('arsip.dokumen.show');

            // ← route download terpisah (force attachment, tidak inline)
            Route::get(
                '/arsip/{id}/dokumen-download',
                [SuperAdminController::class, 'downloadDokumen']
            )->name('arsip.dokumen.download');

            Route::get(
                '/activity-logs',
                [SuperAdminController::class, 'activityLogs']
            )->name('activity.logs');

            Route::get(
                '/histori',
                [SuperAdminController::class, 'historiAktivitas']
            )->name('histori');

            /*
|--------------------------------------------------------------------------
| KELOLA MENU
|--------------------------------------------------------------------------
*/

            Route::get(
                '/kelola-menu',
                [SuperAdminController::class, 'kelolaMenu']
            )->name('kelola.menu');

            /*
|--------------------------------------------------------------------------
| CRUD MASTER MENU
|--------------------------------------------------------------------------
*/

            Route::post(
                '/kelola-menu/{type}',
                [SuperAdminController::class, 'storeMenu']
            )->name('kelola.menu.store');

            Route::put(
                '/kelola-menu/{type}/{id}',
                [SuperAdminController::class, 'updateMenu']
            )->name('kelola.menu.update');

            Route::delete(
                '/kelola-menu/{type}/{id}',
                [SuperAdminController::class, 'destroyMenu']
            )->name('kelola.menu.destroy');

            Route::patch(
                '/kelola-menu/{type}/{id}/toggle',
                [SuperAdminController::class, 'toggleMenuStatus']
            )->name('kelola.menu.toggle');

            Route::get('/kelola-akun', [SuperAdminController::class, 'kelolaAkun'])->name('kelola.akun');
            Route::put('/kelola-akun', [SuperAdminController::class, 'updateAkun'])
                ->name('akun.update');

            Route::get('/kelola-akun/ppk', [SuperAdminController::class, 'kelolaAkunPpk'])->name('kelola.akun.ppk');
            Route::post('/kelola-akun/ppk', [SuperAdminController::class, 'storePpk'])->name('kelola.akun.ppk.store');
            Route::put('/kelola-akun/ppk/{id}', [SuperAdminController::class, 'updatePpk'])->name('kelola.akun.ppk.update');
            Route::delete('/kelola-akun/ppk/{id}', [SuperAdminController::class, 'destroyPpk'])->name('kelola.akun.ppk.destroy');

            Route::get('/kelola-akun/unit', [SuperAdminController::class, 'kelolaAkunUnit'])->name('kelola.akun.unit');
            Route::post('/kelola-akun/unit', [SuperAdminController::class, 'storeUnit'])->name('kelola.akun.unit.store');
            Route::put('/kelola-akun/unit/{id}', [SuperAdminController::class, 'updateUnit'])->name('kelola.akun.unit.update');
            Route::delete('/kelola-akun/unit/{id}', [SuperAdminController::class, 'destroyUnit'])->name('kelola.akun.unit.destroy');
        });
});
