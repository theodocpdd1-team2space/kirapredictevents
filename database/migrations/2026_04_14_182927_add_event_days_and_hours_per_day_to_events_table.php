<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'event_days')) {
                $table->unsignedInteger('event_days')->default(1)->after('duration');
            }
            if (!Schema::hasColumn('events', 'hours_per_day')) {
                $table->unsignedInteger('hours_per_day')->default(1)->after('event_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'hours_per_day')) {
                $table->dropColumn('hours_per_day');
            }
            if (Schema::hasColumn('events', 'event_days')) {
                $table->dropColumn('event_days');
            }
        });
    }
};