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
    // users
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // inventories
    if (\Illuminate\Support\Facades\Schema::hasColumn('inventories', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('inventories', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // rules
    if (\Illuminate\Support\Facades\Schema::hasColumn('rules', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('rules', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // events
    if (\Illuminate\Support\Facades\Schema::hasTable('events') && \Illuminate\Support\Facades\Schema::hasColumn('events', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('events', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // estimations
    if (\Illuminate\Support\Facades\Schema::hasTable('estimations') && \Illuminate\Support\Facades\Schema::hasColumn('estimations', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('estimations', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // estimation_details
    if (\Illuminate\Support\Facades\Schema::hasTable('estimation_details') && \Illuminate\Support\Facades\Schema::hasColumn('estimation_details', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('estimation_details', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // settings
    if (\Illuminate\Support\Facades\Schema::hasTable('settings') && \Illuminate\Support\Facades\Schema::hasColumn('settings', 'tenant_id')) {
        \Illuminate\Support\Facades\Schema::table('settings', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    // drop tenants table
    if (\Illuminate\Support\Facades\Schema::hasTable('tenants')) {
        \Illuminate\Support\Facades\Schema::drop('tenants');
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
