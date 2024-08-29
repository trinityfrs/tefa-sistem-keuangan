<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryLayananRequest;
use App\Models\LaundryLayanan;
use illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Symfony\Component\HttpFoundation\Response;

class LaundryLayananController extends Controller
{
    const IMAGE_STORAGE_PATH = 'public/laundry/layanan/';

    public function index()
    {
        $usaha = Auth::user()->usaha->first();
        $perPage = request('per_page', 10);
        $namaLayanan = request('nama_layanan');

        try {
            $layanan = $usaha->laundry_layanan()
                ->when($namaLayanan, function ($query) use ($namaLayanan) {
                    $query->where('nama_layanan', 'like', "%$namaLayanan%");
                })
                ->latest()->paginate($perPage);
            return response()->json(['data' => $layanan], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(LaundryLayananRequest $request)
    {
        $usaha = Auth::user()->usaha->first();
        $fields = $request->validated();

        try {
            $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_layanan']);
            $fields['foto_layanan'] = basename($path);
            $fields['usaha_id'] = $usaha->id;
            $fields['satuan'] = $fields['tipe'] == 'satuan' ? 'pcs' : 'kg';
            $layanan = LaundryLayanan::create($fields);
            return response()->json(['data' => $layanan], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat membuat data layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(LaundryLayanan $layanan)
    {
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function update(LaundryLayananRequest $request, LaundryLayanan $layanan)
    {
        $fields = array_filter($request->validated());

        try {
            if (isset($fields['foto_layanan'])) {
                $path = Storage::putFile(self::IMAGE_STORAGE_PATH, $fields['foto_layanan']);
                Storage::delete(self::IMAGE_STORAGE_PATH . $layanan->foto_layanan);
                $fields['foto_layanan'] = basename($path);
            }
            $fields['satuan'] = $fields['tipe'] == 'satuan' ? 'pcs' : 'kg';
            $layanan->update($fields);
            return response()->json(['data' => $layanan], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengubah data layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(LaundryLayanan $layanan)
    {
        try {
            Storage::delete(self::IMAGE_STORAGE_PATH . $layanan->foto_layanan);
            $layanan->delete();
            return response(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus data layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}