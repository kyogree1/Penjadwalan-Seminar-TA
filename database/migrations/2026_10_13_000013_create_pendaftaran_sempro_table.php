<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_sempro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_ta');
            $table->string('bentuk_ta'); // Skripsi Reguler, dsb
            $table->string('lokasi_mitra')->nullable();

            // File upload paths
            $table->string('lembar_kehadiran_file')->nullable();
            $table->string('proposal_file')->nullable();
            $table->string('turnitin_file')->nullable();
            $table->string('iaet_file')->nullable(); // Skor IAET

            // Gelombang pendaftaran (1, 2, 3...)
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
        Schema::dropIfExists('pendaftaran_sempro');
    }
};
