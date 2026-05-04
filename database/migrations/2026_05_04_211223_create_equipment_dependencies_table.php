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
        if (Schema::hasTable('equipment_dependencies')) {
            return;
        }

        Schema::create('equipment_dependencies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->string('trigger_equipment_name', 255);
            $table->string('required_equipment_name', 255);

            $table->unsignedInteger('quantity')->default(1);
            $table->string('reason', 255)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'trigger_equipment_name']);
            $table->unique([
                'tenant_id',
                'trigger_equipment_name',
                'required_equipment_name',
            ], 'equipment_dependencies_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_dependencies');
    }
};