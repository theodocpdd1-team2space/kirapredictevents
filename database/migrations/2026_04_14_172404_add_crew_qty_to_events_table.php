<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Nullable = kalau null berarti "auto (dari rules)"
            // Kalau user isi 0, berarti override jadi 0 (no crew)
            if (!Schema::hasColumn('events', 'crew_operator_qty')) {
                $table->unsignedInteger('crew_operator_qty')->nullable()->after('special_requirement');
            }
            if (!Schema::hasColumn('events', 'crew_engineer_qty')) {
                $table->unsignedInteger('crew_engineer_qty')->nullable()->after('crew_operator_qty');
            }
            if (!Schema::hasColumn('events', 'crew_stage_qty')) {
                $table->unsignedInteger('crew_stage_qty')->nullable()->after('crew_engineer_qty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'crew_stage_qty')) {
                $table->dropColumn('crew_stage_qty');
            }
            if (Schema::hasColumn('events', 'crew_engineer_qty')) {
                $table->dropColumn('crew_engineer_qty');
            }
            if (Schema::hasColumn('events', 'crew_operator_qty')) {
                $table->dropColumn('crew_operator_qty');
            }
        });
    }
};