<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kantin_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kantin_id');
            $table->unsignedInteger('jumlah_pengajuan');
            $table->enum('status',['pending','disetujui','ditolak'])->default('pending');
            $table->string('alasan_penolakan')->nullable();
            $table->dateTime('tanggal_pengajuan')->default(now());
            $table->dateTime('tanggal_selesai')->nullable();
            $table->timestamps();
            
            $table->foreign('kantin_id')->references('id')->on('kantin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin_pengajuan');
    }
};