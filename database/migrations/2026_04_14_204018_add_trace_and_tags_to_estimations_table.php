<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            // trace decision tree / rules
            if (!Schema::hasColumn('estimations', 'trace_json')) {
                $table->json('trace_json')->nullable()->after('breakdown');
            }

            // parsed tags hasil parser special_requirement
            if (!Schema::hasColumn('estimations', 'parsed_tags')) {
                $table->json('parsed_tags')->nullable()->after('trace_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (Schema::hasColumn('estimations', 'parsed_tags')) {
                $table->dropColumn('parsed_tags');
            }
            if (Schema::hasColumn('estimations', 'trace_json')) {
                $table->dropColumn('trace_json');
            }
        });
    }
};