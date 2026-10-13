<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('tipe', ['sempro', 'sidang']);
            $table->unsignedInteger('gelombang');
            $table->unsignedInteger('capacity');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('slots');
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->constrained('schedule_periods')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penjadwalan', fn (Blueprint $table) => $table->dropConstrainedForeignId('period_id'));
        Schema::dropIfExists('schedule_periods');
    }
};
