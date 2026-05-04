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
        if (Schema::hasTable('capacity_tiers')) {
            return;
        }

        Schema::create('capacity_tiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->string('key', 80);
            $table->string('label', 150);

            $table->unsignedInteger('min_participants');
            $table->unsignedInteger('max_participants')->nullable();

            $table->unsignedInteger('watt_min')->nullable();
            $table->unsignedInteger('watt_max')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'min_participants', 'max_participants']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacity_tiers');
    }
};