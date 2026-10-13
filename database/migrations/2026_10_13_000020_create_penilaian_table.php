<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjadwalan_id')->constrained('penjadwalan')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->restrictOnDelete();
            $table->decimal('nilai', 5, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['penjadwalan_id', 'dosen_id']);
        });
        if (DB::getDriverName() === 'sqlite') {
            foreach (['INSERT', 'UPDATE'] as $action) {
                DB::statement("CREATE TRIGGER penilaian_range_{$action} BEFORE {$action} ON penilaian WHEN NEW.nilai < 0 OR NEW.nilai > 100 OR ROUND(NEW.nilai, 2) != NEW.nilai BEGIN SELECT RAISE(ABORT, 'nilai must be 0-100 with at most two decimals'); END");
            }
        } else {
            DB::statement('ALTER TABLE penilaian ADD CONSTRAINT penilaian_range CHECK (nilai >= 0 AND nilai <= 100)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
