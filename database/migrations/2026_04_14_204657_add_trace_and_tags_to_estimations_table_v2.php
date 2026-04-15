<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            // jangan pakai hasColumn di sini biar "pasti nambah"
            $table->json('trace_json')->nullable()->after('breakdown');
            $table->json('parsed_tags')->nullable()->after('trace_json');
        });
    }

    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            $table->dropColumn(['parsed_tags', 'trace_json']);
        });
    }
};