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

        if (Schema::hasTable('inventories')) return;
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_name')->unique();
            $table->string('category');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('price'); // unit price
            $table->enum('status', ['active','maintenance','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
