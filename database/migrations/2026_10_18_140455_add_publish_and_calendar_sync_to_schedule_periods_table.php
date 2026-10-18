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
            $table->boolean('is_published')->default(false)->after('is_open');
            $table->boolean('google_calendar_synced')->default(false)->after('is_published');
            $table->timestamp('google_calendar_synced_at')->nullable()->after('google_calendar_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_periods', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'google_calendar_synced', 'google_calendar_synced_at']);
        });
    }
};
