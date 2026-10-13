<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_sidang') && !Schema::hasColumn('pendaftaran_sidang', 'skor_iaet')) {
            Schema::table('pendaftaran_sidang', function (Blueprint $table) {
                $table->unsignedInteger('skor_iaet')->nullable()->after('lokasi_mitra');
            });
        }

        if (Schema::hasTable('pendaftaran_sempro') && !Schema::hasColumn('pendaftaran_sempro', 'skor_iaet')) {
            Schema::table('pendaftaran_sempro', function (Blueprint $table) {
                $table->unsignedInteger('skor_iaet')->nullable()->after('lokasi_mitra');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pendaftaran_sidang') && Schema::hasColumn('pendaftaran_sidang', 'skor_iaet')) {
            Schema::table('pendaftaran_sidang', function (Blueprint $table) {
                $table->dropColumn('skor_iaet');
            });
        }

        if (Schema::hasTable('pendaftaran_sempro') && Schema::hasColumn('pendaftaran_sempro', 'skor_iaet')) {
            Schema::table('pendaftaran_sempro', function (Blueprint $table) {
                $table->dropColumn('skor_iaet');
            });
        }
    }
};
