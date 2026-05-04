<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 1. Create tenants table
         */
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        /**
         * 2. Create default tenant
         */
        $tenantId = DB::table('tenants')->where('slug', 'wijaya-music')->value('id');

        if (!$tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Wijaya Music',
                'slug' => 'wijaya-music',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /**
         * 3. Add tenant_id to users
         */
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->nullOnDelete();
            });
        }

        /**
         * 4. Normalize role column
         */
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('staff')->after('password');
            });
        }

        DB::table('users')->update(['tenant_id' => $tenantId]);

        DB::table('users')
            ->where('email', 'admin@local.test')
            ->update(['role' => 'owner', 'tenant_id' => $tenantId]);

        DB::table('users')
            ->where('email', 'theofilus267@gmail.com')
            ->update(['role' => 'staff', 'tenant_id' => $tenantId]);

        /**
         * 5. Add tenant_id to tenant-owned tables
         */
        $tables = [
            'events',
            'estimations',
            'inventories',
            'rules',
            'settings',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('tenant_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('tenants')
                        ->nullOnDelete();
                });

                DB::table($tableName)->update(['tenant_id' => $tenantId]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'settings',
            'rules',
            'inventories',
            'estimations',
            'events',
            'users',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tenant_id');
                });
            }
        }

        Schema::dropIfExists('tenants');
    }
};