<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (!Schema::hasColumn('estimations', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('id');
                $table->index('created_by');
            }
        });

        /**
         * Backfill: isi created_by dari event.created_by (kalau event_id sudah ada)
         * Aman kalau estimations sudah berisi data lama.
         */
        if (
            Schema::hasColumn('estimations', 'event_id') &&
            Schema::hasColumn('events', 'created_by') &&
            Schema::hasColumn('estimations', 'created_by')
        ) {
            DB::statement("
                UPDATE estimations e
                JOIN events ev ON ev.id = e.event_id
                SET e.created_by = ev.created_by
                WHERE e.created_by IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            if (Schema::hasColumn('estimations', 'created_by')) {
                $table->dropIndex(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};