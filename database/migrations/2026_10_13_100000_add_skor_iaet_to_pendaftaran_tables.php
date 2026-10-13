<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom skor_iaet (nilai numerik IAET/TOEFL) ke tabel
 * pendaftaran_sempro dan pendaftaran_sidang.
 *
 * Kolom ini sudah ada di validasi kedua controller tapi belum ada di
 * database — menyebabkan nilai tidak pernah tersimpan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_sempro', function (Blueprint $table) {
            // Skor numerik IAET/TOEFL mahasiswa (0–990 atau 0–120).
            // Nullable karena tidak semua mahasiswa wajib lampirkan.
            $table->unsignedSmallInteger('skor_iaet')->nullable()->after('iaet_file');
        });

        Schema::table('pendaftaran_sidang', function (Blueprint $table) {
            $table->unsignedSmallInteger('skor_iaet')->nullable()->after('skor_iaet_file');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_sempro', function (Blueprint $table) {
            $table->dropColumn('skor_iaet');
        });

        Schema::table('pendaftaran_sidang', function (Blueprint $table) {
            $table->dropColumn('skor_iaet');
        });
    }
};
