<?php

use App\Http\Controllers\Admin\AdminCutiController;
use App\Http\Controllers\Admin\AdminPegawaiController;
use App\Http\Controllers\Admin\AdminPenggajianController;
use App\Http\Controllers\Admin\AdminPresensiController;
use App\Http\Controllers\Admin\AdminReimburseController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ReimburseController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// User
Route::get('/users', [UserController::class, 'index']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/change-password', [UserController::class, 'changePassword']);


// Presensi
Route::get('/presensi', [PresensiController::class, 'index']);
Route::get('/presensi/user/{userId}', [PresensiController::class, 'byUser']);
Route::get('/presensi-today/user/{userId}', [PresensiController::class, 'getPresensiToday']);
Route::post('/presensi/attend', [PresensiController::class, 'presensi']);

// Perusahaan
Route::get('/perusahaan', [PerusahaanController::class, 'index']);

//pegawai
Route::get('/pegawai/{userId}', [PegawaiController::class, 'getPegawai']);
Route::get('/pegawai/', [PegawaiController::class, 'getPegawai']);
Route::get('/pegawai/image/{pegawaiId}', [PegawaiController::class, 'getPhotoByPegawaiId']);
Route::post('/pegawai/update-profile-picture', [PegawaiController::class, 'imageStore']);
Route::put('/pegawai/update-profile', [PegawaiController::class, 'updateProfile']);



//penggajian
Route::get('/penggajian/{userId}', [PenggajianController::class, 'getAllPayRollByUser']);

//reimburse
Route::get('/reimburse/{userId}', [ReimburseController::class, 'getAllReimburseByUser']);
Route::get('/reimburse/lampiran/{id_reimburse}', [ReimburseController::class, 'showLampiranById']);
Route::post('/reimburse/lampiran', [ReimburseController::class, 'storeLampiranReimburseById']);
Route::post('/reimburse/create', [ReimburseController::class, 'storeReimburseByUser']);

//Cuti
Route::controller(CutiController::class)->group(function(){
    Route::get('/cutis/{userId}', 'findByUser');
    Route::get('/cuti/{cutiId}', 'getDetailCuti');
    Route::post('/cuti/add', 'addCuti');
    Route::get('/cuti-sisa/{userId}', 'getAddData');
    Route::put('/cuti/approve/{cutiId}','approveCuti');
    Route::put('/cuti/reject/{cutiId}','rejectCuti');

});

Route::prefix('admin')->group(function() {
    Route::prefix('pegawai')->group(function(){
        Route::controller(AdminPegawaiController::class)->group(function(){
            Route::get('', 'getPegawai');
             Route::get('/{id}', 'getPegawaiById');
            Route::post('/add', 'addPegawai');
            Route::put('/update', 'updatePegawai');
            Route::post('/remove', 'removePegawai');
            Route::get('/image/{id}', 'getPhotoByPegawaiId');
           
        });
    });

    Route::prefix('penggajian')->group(function(){
        Route::controller(AdminPenggajianController::class)->group(function(){
            Route::get('', 'getPenggajian');
            Route::post('/add', 'addPenggajian');
            Route::post('/remove', 'removePenggajian');
        });
    });

    Route::prefix('reimburse')->group(function(){
        Route::controller(AdminReimburseController::class)->group(function(){
            Route::get('', 'getReimburse');
            Route::post('/add', 'addReimburse');
            Route::put('/update', 'updateReimburse');
            Route::post('/remove', 'removeReimburse');
            Route::get('/image/{id}', 'showLampiranById');
        });
    });

    Route::prefix('cuti')->group(function(){
        Route::controller(AdminCutiController::class)->group(function(){
            Route::get('', 'getCuti');
            Route::post('/add', 'addCuti');
            Route::put('/update', 'updateCuti');
        });
    });

    Route::prefix('presensi')->group(function(){
        Route::controller(AdminPresensiController::class)->group(function(){
            Route::get('', 'getPresensi');
            Route::post('/add', 'addPresensi');
            Route::put('/update', 'updatePresensi');
        });
    });
});
