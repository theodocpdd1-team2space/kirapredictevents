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
        Schema::table('estimation_details', function (Blueprint $table) {
            if (!Schema::hasColumn('estimation_details', 'is_removed')) {
                $table->boolean('is_removed')
                    ->default(false)
                    ->after('is_custom');
            }

            if (!Schema::hasColumn('estimation_details', 'removed_at')) {
                $table->timestamp('removed_at')
                    ->nullable()
                    ->after('is_removed');
            }

            if (!Schema::hasColumn('estimation_details', 'removed_by')) {
                $table->unsignedBigInteger('removed_by')
                    ->nullable()
                    ->after('removed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimation_details', function (Blueprint $table) {
            if (Schema::hasColumn('estimation_details', 'removed_by')) {
                $table->dropColumn('removed_by');
            }

            if (Schema::hasColumn('estimation_details', 'removed_at')) {
                $table->dropColumn('removed_at');
            }

            if (Schema::hasColumn('estimation_details', 'is_removed')) {
                $table->dropColumn('is_removed');
            }
        });
    }
};