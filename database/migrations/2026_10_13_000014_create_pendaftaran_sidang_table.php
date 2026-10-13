<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_ta');
            $table->string('lokasi_mitra')->nullable();

            // File upload paths
            $table->string('skor_iaet_file')->nullable();
            $table->string('draft_laporan_file')->nullable();
            $table->string('turnitin_file')->nullable();

            $table->integer('gelombang')->default(1);

            // Status alur: menunggu → verifikasi_tendik → terjadwal → selesai → ditolak
            $table->enum('status', [
                'menunggu',
                'verifikasi_tendik',
                'terjadwal',
                'selesai',
                'ditolak',
            ])->default('menunggu');

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_sidang');
    }
};
