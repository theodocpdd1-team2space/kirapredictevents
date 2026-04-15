<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('client_name', 150)->nullable()->after('event_name');
            $table->string('client_whatsapp', 30)->nullable()->after('client_name');

            // kalau kamu mau simpan pilihan + "other"
            $table->string('event_type_choice', 50)->nullable()->after('event_type');
            $table->string('event_type_other', 100)->nullable()->after('event_type_choice');

            $table->string('location_choice', 50)->nullable()->after('location');
            $table->string('location_other', 100)->nullable()->after('location_choice');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'client_name',
                'client_whatsapp',
                'event_type_choice',
                'event_type_other',
                'location_choice',
                'location_other',
            ]);
        });
    }
};