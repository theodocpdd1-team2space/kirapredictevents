<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('rules', function (Blueprint $table) {
      $table->foreignId('tenant_id')
        ->nullable()
        ->after('id')
        ->constrained('tenants')
        ->cascadeOnDelete();

      // optional: aktif/nonaktif
      // $table->boolean('is_active')->default(true)->after('priority');
    });
  }

  public function down(): void
  {
    Schema::table('rules', function (Blueprint $table) {
      // $table->dropColumn('is_active');
      $table->dropConstrainedForeignId('tenant_id');
    });
  }
};