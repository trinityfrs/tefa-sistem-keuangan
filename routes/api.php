<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PembayaranSiswaController;
use App\Http\Controllers\PembayaranKategoriController;
use App\Http\Controllers\PembayaranDuitkuController;
use App\Http\Controllers\PembayaranController;
// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);


    // pengeluaran
    Route::post('pengeluaran/kategori', [PengeluaranController::class, 'addPengeluaranKategori']);

});

Route::get('orangtua', [OrangTuaController::class, 'getAllSekolah']);
Route::get('orangtua/{id}', [OrangTuaController::class, 'show']);
Route::post('orangtua', [OrangTuaController::class, 'store']);
Route::patch('orangtua/{id}', [OrangTuaController::class, 'update']);
Route::delete('orangtua/{id}', [OrangTuaController::class, 'destroy']);


Route::post('sekolah', [SekolahController::class, 'store']);

// get sekolah
Route::get('sekolah', [SekolahController::class, 'getAllSekolah']);

// update sekolah
Route::put('/sekolah/{id}', [SekolahController::class, 'update']);

// delete sekolah
Route::delete('/sekolah/{id}', [SekolahController::class, 'destroy']);
Route::get('sekolah/{id}', [SekolahController::class, 'show']);


// kelas crud
Route::get('kelas', [KelasController::class, 'index']);
Route::get('kelas/{id}', [KelasController::class, 'show']);
Route::post('kelas', [KelasController::class, 'store']);
Route::put('kelas/{id}', [KelasController::class, 'update']);
Route::delete('kelas/{id}', [KelasController::class, 'destroy']);



// data siswa
Route::get('siswa', [SiswaController::class, 'getAllSiswa']);
Route::get('siswa/{id}', [SiswaController::class, 'show']);
Route::post('siswa', [SiswaController::class, 'store']);
Route::put('siswa/{id}', [SiswaController::class, 'updateSiswa']);
Route::delete('siswa/{id}', [SiswaController::class, 'destroy']);
// close data siswa

// sortir kelas
Route::get('filter-kelas', [KelasController::class, 'filterKelas']);

// sortir sekolah
Route::get('/filter-sekolah', [KelasController::class, 'filterBySekolah']);

// sortir orang tua
Route::get('filter-orangtua/{id}', [SiswaController::class, 'filterByOrangTua']);


// pembayaran
Route::resource('pembayaran_siswa', PembayaranSiswaController::class);
Route::resource('pembayaran_duitku', PembayaranDuitkuController::class);
Route::resource('pembayaran', PembayaranController::class);


Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
Route::post('/pembayaran-siswa', [PembayaranSiswaController::class, 'store']);
Route::get('/pembayaran-siswa/{id}', [PembayaranSiswaController::class, 'show']);
Route::put('/pembayaran-siswa/{id}', [PembayaranSiswaController::class, 'update']);
Route::delete('/pembayaran-siswa/{id}', [PembayaranSiswaController::class, 'destroy']);