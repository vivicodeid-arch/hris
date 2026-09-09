<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "api" middleware group. Make something great! | */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/presensimachine', App\Http\Controllers\Api\PresensiController::class);
Route::post('/presensi/log', [App\Http\Controllers\Api\PresensiController::class, 'log']);

// Endpoint fingerprint tanpa rate limiting
// Karena sudah ada mekanisme duplikasi via cache di controller
// dan mesin fingerprint perlu mengirim data real-time tanpa batasan
Route::post('/presensi/receive-data', [App\Http\Controllers\Api\PresensiController::class, 'receiveRevoData'])
    ->withoutMiddleware('throttle:api');

// Endpoint untuk capture data mentah ADMS
Route::any('/adms/capture', [App\Http\Controllers\Api\AdmsController::class, 'capture'])
    ->withoutMiddleware('throttle:api');

// Endpoint untuk polling perintah dan sinkronisasi waktu dari mesin ADMS
Route::any('/iclock/getrequest', [App\Http\Controllers\Api\AdmsController::class, 'getrequest'])
    ->withoutMiddleware('throttle:api');

// Endpoint khusus untuk cek data mentah dari mesin (debug only)
Route::any('/rawdump/{any?}', [App\Http\Controllers\Api\AdmsController::class, 'rawDump'])
    ->where('any', '.*')
    ->withoutMiddleware('throttle:api');

// Endpoint untuk menerima data dari mesin Fingerspot REVO melalui ADMS
// Route::post('/presensi/revo', [App\Http\Controllers\Api\PresensiController::class, 'receiveRevoData'])
//     ->withoutMiddleware('throttle:api');

// Endpoint khusus untuk test X100C Solution


// Route::any('/iclock/cdata', [App\Http\Controllers\Api\AdmsController::class, 'testX100c'])
//     ->withoutMiddleware('throttle:api');
// Update API Routes
Route::prefix('update')->group(function () {
    // Public endpoints (tidak perlu auth) - Route spesifik dulu
    Route::get('/check', [App\Http\Controllers\Api\UpdateController::class, 'checkUpdate']);
    Route::get('/version', [App\Http\Controllers\Api\UpdateController::class, 'getCurrentVersion']);
    Route::get('/list', [App\Http\Controllers\Api\UpdateController::class, 'listUpdates']);

    // Protected endpoints (disarankan menggunakan auth) - Route spesifik dulu
    Route::middleware('auth:sanctum')->group(
        function () {
            Route::get('/history', [App\Http\Controllers\Api\UpdateController::class, 'history']);
            Route::get('/log/{id}', [App\Http\Controllers\Api\UpdateController::class, 'showLog']);
            Route::get('/status/{logId}', [App\Http\Controllers\Api\UpdateController::class, 'getStatus']);
            Route::post('/{version}/download', [App\Http\Controllers\Api\UpdateController::class, 'downloadUpdate']);
            Route::post('/{version}/install', [App\Http\Controllers\Api\UpdateController::class, 'installUpdate']);
            Route::post('/{version}/update-now', [App\Http\Controllers\Api\UpdateController::class, 'updateNow']);
        }
    );

    // Route dengan parameter di akhir (agar tidak conflict)
    Route::get('/{version}', [App\Http\Controllers\Api\UpdateController::class, 'show']);
});

// Mobile API Routes
Route::prefix('mobile')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\Mobile\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\Mobile\AuthController::class, 'logout']);
        Route::get('/profile', [App\Http\Controllers\Api\Mobile\ProfileController::class, 'index']);
        Route::post('/profile/password', [App\Http\Controllers\Api\Mobile\ProfileController::class, 'updatePassword']);
        Route::post('/profile/foto', [App\Http\Controllers\Api\Mobile\ProfileController::class, 'updateFoto']);
        Route::get('/dashboard', [App\Http\Controllers\Api\Mobile\DashboardController::class, 'index']);
        Route::get('/jadwal', [App\Http\Controllers\Api\Mobile\DashboardController::class, 'mySchedule']);
        Route::get('/tukarshift', [App\Http\Controllers\Api\Mobile\TukarShiftController::class, 'index']);
        Route::post('/tukarshift', [App\Http\Controllers\Api\Mobile\TukarShiftController::class, 'store']);
        Route::delete('/tukarshift/{id}', [App\Http\Controllers\Api\Mobile\TukarShiftController::class, 'destroy']);
        Route::get('/kpi/myscore', [App\Http\Controllers\Api\Mobile\KpiController::class, 'myScore']);
        Route::post('/kpi/input-realisasi', [App\Http\Controllers\Api\Mobile\KpiController::class, 'inputRealisasi']);
        
        // Project Board API Routes
        Route::get('/projects', [App\Http\Controllers\Api\Mobile\ProjectController::class, 'index']);
        Route::get('/projects/{id}', [App\Http\Controllers\Api\Mobile\ProjectController::class, 'show']);
        Route::post('/projects/tasks/{taskId}/status', [App\Http\Controllers\Api\Mobile\ProjectController::class, 'updateTaskStatus']);

        Route::post('/presensi/masuk', [App\Http\Controllers\Api\Mobile\PresensiController::class, 'masuk']);
        Route::post('/presensi/pulang', [App\Http\Controllers\Api\Mobile\PresensiController::class, 'pulang']);
        Route::post('/presensi/istirahat', [App\Http\Controllers\Api\Mobile\PresensiController::class, 'istirahat']);
        Route::get('/presensi/riwayat', [App\Http\Controllers\Api\Mobile\PresensiController::class, 'riwayat']);
        Route::get('/izin', [App\Http\Controllers\Api\Mobile\IzinController::class, 'index']);
        Route::post('/izin', [App\Http\Controllers\Api\Mobile\IzinController::class, 'store']);
        Route::delete('/izin/{kode}', [App\Http\Controllers\Api\Mobile\IzinController::class, 'destroy']);
        Route::get('/slipgaji', [App\Http\Controllers\Api\Mobile\SlipgajiController::class, 'index']);
        Route::get('/slipgaji/{bulan}/{tahun}', [App\Http\Controllers\Api\Mobile\SlipgajiController::class, 'show']);
        Route::get('/kontrak', [App\Http\Controllers\Api\Mobile\KontrakController::class, 'index']);
        Route::get('/kontrak/{id}', [App\Http\Controllers\Api\Mobile\KontrakController::class, 'show']);
        Route::get('/kontrak/{id}/download', [App\Http\Controllers\Api\Mobile\KontrakController::class, 'download']);

        // Pelanggaran (SP) API Routes
        Route::get('/pelanggaran', [App\Http\Controllers\Api\Mobile\PelanggaranController::class, 'index']);
        Route::get('/pelanggaran/{no_sp}', [App\Http\Controllers\Api\Mobile\PelanggaranController::class, 'show']);

        // Pinjaman (PJP) API Routes
        Route::get('/pinjaman', [App\Http\Controllers\Api\Mobile\PinjamanController::class, 'index']);

        // Reimbursement API Routes
        Route::get('/reimbursement', [App\Http\Controllers\Api\Mobile\ReimbursementController::class, 'index']);
        Route::get('/reimbursement/categories', [App\Http\Controllers\Api\Mobile\ReimbursementController::class, 'getCategories']);
        Route::post('/reimbursement', [App\Http\Controllers\Api\Mobile\ReimbursementController::class, 'store']);
        Route::get('/reimbursement/{id}', [App\Http\Controllers\Api\Mobile\ReimbursementController::class, 'show']);
        Route::delete('/reimbursement/{id}', [App\Http\Controllers\Api\Mobile\ReimbursementController::class, 'destroy']);

        // Face Recognition API Routes
        Route::get('/facerecognition', [App\Http\Controllers\Api\Mobile\FacerecognitionController::class, 'index']);
        Route::post('/facerecognition', [App\Http\Controllers\Api\Mobile\FacerecognitionController::class, 'store']);
        Route::delete('/facerecognition', [App\Http\Controllers\Api\Mobile\FacerecognitionController::class, 'destroy']);

        // Overtime (Lembur) API Routes
        Route::get('/lembur', [App\Http\Controllers\Api\Mobile\LemburController::class, 'index']);
        Route::post('/lembur', [App\Http\Controllers\Api\Mobile\LemburController::class, 'store']);
        Route::post('/lembur/absen', [App\Http\Controllers\Api\Mobile\LemburController::class, 'storepresensi']);

        // Kunjungan (Visit) API Routes
        Route::get('/kunjungan', [App\Http\Controllers\Api\Mobile\KunjunganController::class, 'index']);
        Route::post('/kunjungan', [App\Http\Controllers\Api\Mobile\KunjunganController::class, 'store']);

        // Aktivitas (Activity) API Routes
        Route::get('/aktivitas', [App\Http\Controllers\Api\Mobile\AktivitasController::class, 'index']);
        Route::post('/aktivitas', [App\Http\Controllers\Api\Mobile\AktivitasController::class, 'store']);
        Route::delete('/aktivitas/{id}', [App\Http\Controllers\Api\Mobile\AktivitasController::class, 'destroy']);
    });
});

