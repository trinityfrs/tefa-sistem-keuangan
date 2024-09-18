<?php

use App\Http\Controllers\Api\AnggaranController;
use App\Http\Controllers\Api\MonitoringController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Models\Anggaran;
use App\Models\User;

// Register and Login Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::get('select/principal', fn() => response()->json([
    "sucess" => true,
    "message" => "Berhasil mendapatkan kepala sekolah",
    "data" => User::where('role', 'Kepala Sekolah')->get()
]));
Route::group([
    'middleware' => ['auth:api'],
], function () {

    Route::post('logout', [LogoutController::class, 'logout']);

    // Role: Admin
    Route::group([
        'middleware' => ['checkrole:Admin'],
        'prefix' => 'Admin'
    ], function () {
        // CRUD Routes
        Route::post('/anggaran', [AnggaranController::class, 'store']);
        Route::get('/anggaran', [AnggaranController::class, 'index']);
        Route::get('/anggaran/chart-data', [AnggaranController::class, 'getAnggaranData']);
        Route::patch('/anggaran/{anggaran}', [AnggaranController::class, 'update']);

        // Additional Routes
        Route::get('/monitoring', [MonitoringController::class, 'getLastSevenAnggaran']);

        // Laporan Anggaran
        Route::get('/laporan/anggaran', function () {
            $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Anggaran.pdf";
            }

            $data = ['anggarans' => $anggaran];
            $pdf = Pdf::loadView('print.anggaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.anggaran');

         // Laporan Deviasi
         Route::get('/laporan/deviasi', [AnggaranController::class, 'printDeviasi'])->name('laporan.deviasi');
    });

    // Role: Bendahara
    Route::group([
        'middleware' => ['checkrole:Bendahara'],
        'prefix' => 'Bendahara'
    ], function () {
        // CRUD Routes
        Route::post('/anggaran', [AnggaranController::class, 'store']);
        Route::get('/anggaran', [AnggaranController::class, 'index']);
        Route::get('/anggaran/chart-data', [AnggaranController::class, 'getAnggaranData']);
        Route::patch('/anggaran/{anggaran}', [AnggaranController::class, 'update']);
        Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy']);

        // Laporan Anggaran
        Route::get('/laporan/anggaran', function () {
            $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Anggaran {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Anggaran.pdf";
            }

            $data = ['anggarans' => $anggaran];
            $pdf = Pdf::loadView('print.anggaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.anggaran');
        Route::get('/laporan/deviasi', [AnggaranController::class, 'printDeviasi'])->name('laporan.deviasi');
    });

    // Role: Kepala Sekolah
    Route::group([
        'middleware' => ['checkrole:Kepala Sekolah'],
        'prefix' => 'Kepala Sekolah'
    ], function () {
        // Read Routes
        Route::post('/anggaran', [AnggaranController::class, 'store']);
        Route::get('/anggaran', [AnggaranController::class, 'index']);
        Route::patch('/anggaran/{anggaran}', [AnggaranController::class, 'update']);
        Route::get('/anggaran/chart-data', [AnggaranController::class, 'getAnggaranData']);
        Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy']);

        // Laporan Anggaran
        Route::get('/laporan/anggaran', function () {
            $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Anggaran {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Anggaran.pdf";
            }

            $data = ['anggarans' => $anggaran];
            $pdf = Pdf::loadView('print.anggaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.anggaran');

        Route::get('/laporan/deviasi', [AnggaranController::class, 'printDeviasi'])->name('laporan.deviasi');
    });
});
