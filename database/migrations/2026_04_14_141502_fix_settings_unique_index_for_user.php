<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // 1) pastikan user_id ada
            if (!Schema::hasColumn('settings', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id', 'settings_user_id_index');
            }

            // 2) drop UNIQUE lama yang namanya benar
            // dari output kamu: settings_user_id_key_unique
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('settings');

            if (isset($indexes['settings_user_id_key_unique'])) {
                $table->dropUnique('settings_user_id_key_unique');
            }

            // 3) bikin UNIQUE yang benar (user_id + key)
            // kasih nama sama biar konsisten
            $table->unique(['user_id', 'key'], 'settings_user_id_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // drop unique composite
            $table->dropUnique('settings_user_id_key_unique');

            // (optional) kalau mau balikin unique key saja:
            // $table->unique('key', 'settings_key_unique');
        });
    }
};