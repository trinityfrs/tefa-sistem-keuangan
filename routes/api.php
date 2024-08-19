<?php

use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\KantinPengajuanController;
use App\Http\Controllers\KantinProdukController;
use App\Http\Controllers\KantinProdukKategoriController;
use App\Http\Controllers\KantinTransaksiController;
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\LaundryLayananController;
use App\Http\Controllers\LaundryPengajuanController;
use App\Http\Controllers\SiswaKantinController;
use App\Http\Controllers\SiswaLaundryController;
use App\Http\Controllers\SiswaWalletController;
use App\Http\Controllers\TopUpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::post('/duitku/callback', [TopUpController::class, 'callback']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::post('/duitku/get-payment-method', [TopUpController::class, 'getPaymentMethod'])->name('get-payment-method');
    Route::post('/duitku/request-transaksi', [TopUpController::class, 'requestTransaction'])->name('request-transaksi');

    Route::group([
        'prefix' => 'orangtua',
        'middleware' => 'checkrole:OrangTua'
    ], function() {
        Route::group(['prefix' => 'wallet'], function() {
            Route::get('/saldo');
        });
    });

    Route::group([
        'prefix' => 'siswa',
        'middleware' => 'checkrole:Siswa'
    ], function() {
        Route::group(['prefix' => 'wallet'], function() {
            Route::get('/saldo', [SiswaWalletController::class, 'getSaldo']);
            Route::get('/riwayat', [SiswaWalletController::class, 'getRiwayat']);
        });

        Route::group(['prefix' => 'kantin'], function() {
            Route::group(['prefix' => 'produk'], function() {
                Route::get('/', [SiswaKantinController::class, 'getProduk']);
                Route::get('/riwayat', [SiswaKantinController::class, 'getKantinRiwayat']);
                Route::get('/{produk}', [SiswaKantinController::class, 'getProdukDetail']);
                Route::post('/{produk}/transaksi', [SiswaKantinController::class, 'createProdukTransaksi']);
            });
        });
        
        Route::group(['prefix' => 'laundry'], function() {
            Route::group(['prefix' => 'satuan'], function() {
                Route::get('/', [SiswaLaundryController::class, 'getItem']);
                Route::get('/riwayat', [SiswaLaundryController::class, 'getItemRiwayat']);
                Route::post('/transaksi', [SiswaLaundryController::class, 'createItemTransaksi'])->name('siswa-layanan-transaksi');
                Route::get('/{item}', [SiswaLaundryController::class, 'getItemDetail']);
            });

            Route::group(['prefix' => 'kiloan'], function() {
                Route::get('/', [SiswaLaundryController::class, 'getLayanan']);
                Route::get('/riwayat', [SiswaLaundryController::class, 'getLayananRiwayat']);
                Route::get('/{layanan}', [SiswaLaundryController::class, 'getLayananDetail']);
                Route::post('/{layanan}/transaksi', [SiswaLaundryController::class, 'createLayananTransaksi'])->name('siswa-kiloan-transaksi');
            });
        });
    });
    
    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function () {
        //produk crud
        Route::group(['prefix' => 'produk'], function() {
            Route::get('/', [KantinProdukController::class, 'index']);
            Route::post('/', [KantinProdukController::class, 'create']);
            Route::get('/{produk}', [KantinProdukController::class, 'show']);
            Route::put('/{produk}', [KantinProdukController::class, 'update']);
            Route::delete('/{produk}', [KantinProdukController::class, 'destroy']);
        });
        
        //kategori crud
        Route::group(['prefix' => 'kategori'], function() {
            Route::get('/', [KantinProdukKategoriController::class, 'index']);
            Route::post('/', [KantinProdukKategoriController::class, 'create']);
            Route::get('/{kategori}', [KantinProdukKategoriController::class, 'show']);
            Route::put('/{kategori}', [KantinProdukKategoriController::class, 'update']);
            Route::delete('/{kategori}', [KantinProdukKategoriController::class, 'destroy']);
        });
        
        //transaksi
        Route::group(['prefix' => 'transaksi'], function() {
            Route::get('/', [KantinTransaksiController::class, 'index']);
            Route::get('/riwayat');
            Route::put('/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirmInitialTransaction']);
            Route::put('/{transaksi}', [KantinTransaksiController::class, 'update']);
        });

        //pengajuan
        Route::group(['prefix' => 'pengajuan'], function() {
            Route::post('/', [KantinPengajuanController::class, 'create']);
            Route::get('/riwayat', [KantinPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        //item crud
        Route::group(['prefix' => 'item'], function() {
            Route::get('/', [LaundryItemController::class, 'index']);
            Route::post('/', [LaundryItemController::class, 'create']);
            Route::get('/{item}', [LaundryItemController::class, 'show']);
            Route::put('/{item}', [LaundryItemController::class, 'update']);
            Route::delete('/{item}', [LaundryItemController::class, 'destroy']);
        });

        Route::group(['prefix' => 'layanan'], function() {
            Route::get('/', [LaundryLayananController::class, 'index']);
            Route::post('/', [LaundryLayananController::class, 'create']);
            Route::get('/{layanan}', [LaundryLayananController::class, 'show']);
            Route::put('/{layanan}', [LaundryLayananController::class, 'update']);
            Route::delete('/{layanan}', [LaundryLayananController::class, 'destroy']);
        });

        Route::group(['prefix' => 'transaksi'], function() {
            Route::group(['prefix' => 'satuan'], function() {
                Route::get('/');
                Route::put('/{transaksi}/konfirmasi');
                Route::put('/{transaksi}');
            });

            Route::group(['prefix' => 'kiloan'], function() {
                Route::get('/');
                Route::put('/{transaksi}/konfirmasi');
                Route::put('/{transaksi}');
            });
        });
        
        Route::group(['prefix' => 'pengajuan'], function() {
            Route::post('/', [LaundryPengajuanController::class, 'create']);
            Route::get('/riwayat', [LaundryPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'bendahara',
        'middleware' => 'checkrole:Bendahara'
    ], function () {
        Route::get('/laporan-penjualan', [BendaharaController::class, 'index']);
        
        Route::get('/laporan-penjualan/kantin', [BendaharaController::class, 'getKantinTransaksi']);
        Route::get('/laporan-penjualan/laundry-satuan', [BendaharaController::class, 'getLaundryTransaksiSatuan']);
        Route::get('/laporan-penjualan/laundry-kiloan', [BendaharaController::class, 'getLaundryTransaksiKiloan']);

        Route::get('/laporan-pengajuan/kantin', [BendaharaController::class, 'getKantinPengajuan']);
        Route::put('/laporan-pengajuan/kantin/{pengajuan}', [KantinPengajuanController::class, 'update']);
        
        Route::get('/laporan-pengajuan/laundry', [BendaharaController::class, 'getLaundryPengajuan']);
        Route::put('/laporan-pengajuan/laundry/{pengajuan}', [LaundryPengajuanController::class, 'update']);

    });
});

Route::post('/test', function (Request $request) {
    $user = Auth::user();

    $siswa = Auth::user()->orangtua->first()->siswa->find(1);

    return $siswa;
})->middleware('auth:api'); 