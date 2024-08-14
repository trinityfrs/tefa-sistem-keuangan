<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kantin_transaksi';

    protected $fillable = [
        'siswa_id',
        'kantin_id',
        'kantin_produk_id',
        'jumlah',
        'harga',
        'harga_total',
        'status',
        'tanggal_pemesanan',
        'tanggal_selesai',
    ];


    public function kantin_produk()
    {
        return $this->belongsTo(KantinProduk::class, 'kantin_produk_id');
    }
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kantin()
    {
        return $this->belongsTo(Kantin::class, 'kantin_id');
    }

    public function getTanggalSelesaiDayAttribute()
    {
        return $this->tanggal_selesai ? \Carbon\Carbon::parse($this->tanggal_selesai)->format('l') : null;
    }
}
