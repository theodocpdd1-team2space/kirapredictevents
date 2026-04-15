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

        if (Schema::hasTable('estimation_details')) return;
        Schema::create('estimation_details', function (Blueprint $table) {
        $table->id();

        $table->foreignId('estimation_id')
            ->constrained('estimations')
            ->cascadeOnDelete();

        $table->string('equipment_name');
        $table->unsignedInteger('quantity');
        $table->unsignedBigInteger('price');
        $table->unsignedBigInteger('total');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimation_details');
    }
};
