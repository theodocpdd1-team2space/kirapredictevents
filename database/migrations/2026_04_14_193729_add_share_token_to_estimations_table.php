<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (!Schema::hasColumn('estimations', 'share_token')) {
                $table->string('share_token', 64)->nullable()->after('id');
                $table->unique('share_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (Schema::hasColumn('estimations', 'share_token')) {
                $table->dropUnique(['share_token']);
                $table->dropColumn('share_token');
            }
        });
    }
};