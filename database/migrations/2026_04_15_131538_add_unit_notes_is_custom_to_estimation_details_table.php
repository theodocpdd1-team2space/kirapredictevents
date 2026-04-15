<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estimation_details', function (Blueprint $table) {
            if (!Schema::hasColumn('estimation_details', 'unit')) {
                $table->string('unit', 20)->nullable()->after('equipment_name'); // pcs, set, box, meter, day
            }

            if (!Schema::hasColumn('estimation_details', 'notes')) {
                $table->text('notes')->nullable()->after('total');
            }

            if (!Schema::hasColumn('estimation_details', 'is_custom')) {
                $table->boolean('is_custom')->default(false)->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimation_details', function (Blueprint $table) {
            if (Schema::hasColumn('estimation_details', 'is_custom')) $table->dropColumn('is_custom');
            if (Schema::hasColumn('estimation_details', 'notes')) $table->dropColumn('notes');
            if (Schema::hasColumn('estimation_details', 'unit')) $table->dropColumn('unit');
        });
    }
};