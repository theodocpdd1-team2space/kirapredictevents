<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (!Schema::hasColumn('estimations', 'trace_json')) {
                $table->longText('trace_json')->nullable()->after('breakdown');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (Schema::hasColumn('estimations', 'trace_json')) {
                $table->dropColumn('trace_json');
            }
        });
    }
};