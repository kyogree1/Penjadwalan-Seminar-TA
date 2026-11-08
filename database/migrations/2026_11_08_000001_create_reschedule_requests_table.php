<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjadwalan_id')->constrained('penjadwalan')->cascadeOnDelete();
            $table->foreignId('diminta_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('alasan');
            $table->text('catatan')->nullable();
            $table->date('tanggal_usulan')->nullable();
            $table->time('jam_mulai_usulan')->nullable();
            $table->time('jam_selesai_usulan')->nullable();
            $table->string('ruangan_usulan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_kaprodi')->nullable();
            $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diputuskan_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reschedule_requests');
    }
};
