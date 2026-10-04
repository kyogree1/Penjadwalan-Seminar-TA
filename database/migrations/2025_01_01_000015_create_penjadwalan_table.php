<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjadwalan', function (Blueprint $table) {
            $table->id();
            // Referensi ke pendaftaran (sempro atau sidang)
            $table->enum('tipe', ['sempro', 'sidang']);
            $table->unsignedBigInteger('pendaftaran_id'); // FK dinamis ke pendaftaran_sempro / pendaftaran_sidang

            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangan')->nullOnDelete();

            // Tim penguji (relasi ke users role=dosen)
            $table->foreignId('pembimbing_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pembimbing_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('penguji_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('penguji_2_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            $table->enum('mode', ['offline', 'online', 'hybrid'])->default('offline');
            $table->string('link_online')->nullable(); // Zoom/Meet link jika online

            $table->integer('gelombang')->default(1);

            // Status: draft → terpublikasi → selesai → dibatalkan
            $table->enum('status', ['draft', 'terpublikasi', 'selesai', 'dibatalkan'])->default('draft');

            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjadwalan');
    }
};
