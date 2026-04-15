<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            if (!Schema::hasColumn('rules', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
                $table->index('user_id');
            }

            if (!Schema::hasColumn('rules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('priority');
                $table->index('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            if (Schema::hasColumn('rules', 'is_active')) $table->dropColumn('is_active');
            if (Schema::hasColumn('rules', 'user_id')) $table->dropColumn('user_id');
        });
    }
};