<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ", [$table, $indexName]))->isNotEmpty();
    }

    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        if ($this->indexExists('settings', 'settings_user_id_key_unique')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique('settings_user_id_key_unique');
            });
        }

        if (!$this->indexExists('settings', 'settings_tenant_id_key_unique')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique(['tenant_id', 'key'], 'settings_tenant_id_key_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        if ($this->indexExists('settings', 'settings_tenant_id_key_unique')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique('settings_tenant_id_key_unique');
            });
        }

        if (!$this->indexExists('settings', 'settings_user_id_key_unique')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique(['user_id', 'key'], 'settings_user_id_key_unique');
            });
        }
    }
};