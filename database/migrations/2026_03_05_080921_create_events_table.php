<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ✅ skip kalau table sudah ada
        if (Schema::hasTable('events')) {
            return;
        }

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->nullable(); // kalau belum pakai, aman nullable
            $table->string('event_type');
            $table->integer('participants');
            $table->string('location');
            $table->integer('duration');
            $table->string('service_level');
            $table->text('special_requirement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};