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

        if (Schema::hasTable('rules')) return;
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('condition_field'); // participants, location, service_level, ...
            $table->string('operator');        // >, >=, =, !=, contains, in
            $table->string('value');
            $table->text('action');            // JSON string
            $table->string('category')->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
