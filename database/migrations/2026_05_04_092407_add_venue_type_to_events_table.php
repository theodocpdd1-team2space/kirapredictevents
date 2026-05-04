<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('events') && !Schema::hasColumn('events', 'venue_type')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('venue_type')->default('indoor')->after('location_other');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'venue_type')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('venue_type');
            });
        }
    }
};