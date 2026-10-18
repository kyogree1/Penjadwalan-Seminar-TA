<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedule_periods', function (Blueprint $table) {
            $table->string('semester')->default('Gasal')->after('tipe');
            $table->string('prodi')->default('Informatika')->after('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_periods', function (Blueprint $table) {
            $table->dropColumn(['semester', 'prodi']);
        });
    }
};
