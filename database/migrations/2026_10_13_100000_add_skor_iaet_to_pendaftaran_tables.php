<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom skor_iaet (nilai numerik IAET/TOEFL) ke tabel
 * pendaftaran_sempro dan pendaftaran_sidang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_sempro') && ! Schema::hasColumn('pendaftaran_sempro', 'skor_iaet')) {
            Schema::table('pendaftaran_sempro', function (Blueprint $table) {
                // Skor numerik IAET/TOEFL mahasiswa (0–990 atau 0–120).
                $table->unsignedSmallInteger('skor_iaet')->nullable()->after('iaet_file');
            });
        }

        if (Schema::hasTable('pendaftaran_sidang') && ! Schema::hasColumn('pendaftaran_sidang', 'skor_iaet')) {
            Schema::table('pendaftaran_sidang', function (Blueprint $table) {
                $table->unsignedSmallInteger('skor_iaet')->nullable()->after('skor_iaet_file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pendaftaran_sempro') && Schema::hasColumn('pendaftaran_sempro', 'skor_iaet')) {
            Schema::table('pendaftaran_sempro', function (Blueprint $table) {
                $table->dropColumn('skor_iaet');
            });
        }

        if (Schema::hasTable('pendaftaran_sidang') && Schema::hasColumn('pendaftaran_sidang', 'skor_iaet')) {
            Schema::table('pendaftaran_sidang', function (Blueprint $table) {
                $table->dropColumn('skor_iaet');
            });
        }
    }
};
