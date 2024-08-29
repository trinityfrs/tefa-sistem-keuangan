<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaController extends Controller
{
    public function getSiswa()
    {
        $orangtua = Auth::user()->orangtua->firstOrFail();

        try {
            $siswa = $orangtua->siswa()->get();

            return response()->json(['data' => $siswa], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data siswa.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getRiwayatWalletSiswa($id)
    {
        $orangtua = Auth::user()->orangtua->firstOrFail();

        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');

        try {
            $siswa = $orangtua->siswa()
                ->with([
                    'siswa_wallet',
                    'siswa_wallet.siswa_wallet_riwayat' => function ($query) use ($startDate, $endDate) {
                        $query
                            ->select(
                                'siswa_wallet_id',
                                DB::raw('SUM(CASE WHEN tipe_transaksi = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan'),
                                DB::raw('SUM(CASE WHEN tipe_transaksi = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran'),
                            )
                            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                $query->whereBetween('tanggal_riwayat', [
                                    Carbon::parse($startDate)->startOfDay(),
                                    Carbon::parse($endDate)->endOfDay()
                                ]);
                            }, function ($query) {
                                $query->whereBetween('tanggal_riwayat', [
                                    Carbon::now()->startOfMonth(),
                                    Carbon::now()->endOfMonth()
                                ]);
                            })
                            ->groupBy('siswa_wallet_id');
                    }
                ])
                ->findOrFail($id);

            return response()->json([
                'data' => [
                    'id' => $siswa->id,
                    'nama_siswa' => $siswa->nama_siswa,
                    'saldo_siswa' => $siswa->siswa_wallet->nominal,
                    'total_pemasukan' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pemasukan ?? 0,
                    'total_pengeluaran' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pengeluaran ?? 0,
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // return response()->json(['error' => 'Terjadi kesalahan saat mengambil data pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getRiwayatTransaksiSiswa($id)
    {
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();
        $perPage = request('per_page', 10);
        $role = request('role', 'Kantin');

        try {
            $siswa = $orangTua->siswa()->findOrFail($id);

            $riwayat = $role == 'Kantin'
                ? $siswa->kantin_transaksi()
                    ->with('kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,harga_jual')
                    ->whereIn('status', ['dibatalkan', 'selesai'])
                    ->paginate($perPage)
                : $siswa->laundry_transaksi()
                    ->with('laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,harga')
                    ->whereIn('status', ['dibatalkan', 'selesai'])
                    ->paginate($perPage);

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data riwayat: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}