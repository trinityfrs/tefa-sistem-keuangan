<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\SiswaWalletRiwayat;
use Illuminate\Database\Eloquent\Model;
use illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class KantinTransaksiController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function index()
    {
        $usaha = Auth::user()->usaha->firstOrFail();

        $perPage = request()->input('per_page', 10);
        $status = request('status', 'aktif');

        $transaksi = $usaha->kantin_transaksi()
            ->with(['kantin_transaksi_detail.kantin_produk:id,nama_produk', 'siswa:id,nama_depan,nama_belakang'])
            ->when($status ==  'aktif', function($query) {
                $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
            })
            ->when($status ==  'selesai', function($query) {
                $query->whereIn('status', ['selesai', 'dibatalkan']);
            })
            ->paginate($perPage);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function update(KantinTransaksi $transaksi)
    {
        $this->statusService->update($transaksi);
        if ($transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function confirm(KantinTransaksiRequest $request, KantinTransaksi $transaksi)
    {
        $fields = $request->validated();

        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $usaha = $transaksi->usaha;

        DB::beginTransaction();
        $this->statusService->confirmInitialTransaction($fields['confirm'], $transaksi);
        if ($transaksi->status === 'dibatalkan') {
            $harga_total = $transaksi->kantin_transaksi_detail->sum(function ($detail) {
                return $detail->harga * $detail->jumlah;
            });

            $transaksi->update([
                'tanggal_selesai' => now()
            ]);

            $usaha->update([
                'saldo' => $usaha->saldo - $harga_total
            ]);

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal + $harga_total
            ]);

            SiswaWalletRiwayat::create([
                'siswa_wallet_id' => $siswaWallet->id,
                'merchant_order_id' => null,
                'tipe_transaksi' => 'pemasukan',
                'nominal' => $harga_total,
            ]);
        }
        DB::commit();

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
}