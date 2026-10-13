<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_judul', function (Blueprint $table) {
            $table->enum('status', ['draft', 'menunggu', 'disetujui', 'ditolak', 'revisi'])->default('menunggu')->change();
        });
        foreach (['pendaftaran_sempro', 'pendaftaran_sidang'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->enum('status_akademik', ['menunggu', 'disetujui', 'revisi', 'ditolak'])->default('menunggu');
                $table->text('catatan_kaprodi')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['pendaftaran_sempro', 'pendaftaran_sidang'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['status_akademik', 'catatan_kaprodi']));
        }
        // Keep the expanded title enum to avoid losing persisted revision decisions.
    }
};
