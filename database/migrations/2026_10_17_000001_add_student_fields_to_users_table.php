<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('angkatan')->nullable()->after('prodi');
            $table->string('jurusan')->nullable()->after('angkatan');
            $table->string('status_ta')->nullable()->default('Aktif TA')->after('jurusan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['angkatan', 'jurusan', 'status_ta']);
        });
    }
};
