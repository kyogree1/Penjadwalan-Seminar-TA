<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_judul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_ta');
            $table->string('bidang_penelitian');
            $table->foreignId('pembimbing_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pembimbing_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('telah_konsultasi')->default(false);
            // Status: draft → menunggu → disetujui → ditolak
            $table->enum('status', ['draft', 'menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_kaprodi')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_judul');
    }
};
