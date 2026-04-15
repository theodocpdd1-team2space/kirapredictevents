<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function getIndexNames(string $table): array
    {
        $rows = DB::select("SHOW INDEX FROM {$table}");
        $names = [];
        foreach ($rows as $r) {
            if (!empty($r->Key_name)) $names[] = (string) $r->Key_name;
        }
        return array_values(array_unique($names));
    }

    private function findIndexesOnColumn(string $table, string $column): array
    {
        $rows = DB::select("SHOW INDEX FROM {$table}");
        $hits = [];
        foreach ($rows as $r) {
            $key = (string) ($r->Key_name ?? '');
            $col = (string) ($r->Column_name ?? '');
            if ($key !== '' && $col === $column) {
                $hits[$key] = true;
            }
        }
        return array_keys($hits);
    }

    public function up(): void
    {
        // 1) Drop UNIQUE lama yang global (biasanya unique di equipment_name saja)
        //    Kita cari semua index yang memuat kolom equipment_name (termasuk yang mungkin namanya beda-beda)
        $indexes = $this->findIndexesOnColumn('inventories', 'equipment_name');

        // Jangan drop PRIMARY
        $indexes = array_values(array_filter($indexes, fn ($n) => $n !== 'PRIMARY'));

        // Drop dulu index yg kemungkinan adalah unique equipment_name global
        // (nama default Laravel sering: inventories_equipment_name_unique)
        Schema::table('inventories', function (Blueprint $table) use ($indexes) {
            foreach ($indexes as $idxName) {
                // Nanti kita tambahkan unique baru dengan nama yang kita tentukan,
                // jadi kalau index ini kebetulan sudah benar (composite), tetap aman
                // karena di step berikut kita cek dan create yg benar.
                try {
                    $table->dropIndex($idxName);
                } catch (\Throwable $e) {
                    // abaikan kalau index bukan tipe yg bisa di-drop dengan dropIndex di driver tertentu
                }

                try {
                    $table->dropUnique($idxName);
                } catch (\Throwable $e) {
                    // abaikan kalau bukan unique / nama tidak cocok
                }
            }
        });

        // 2) Pastikan ada index unique(user_id, equipment_name)
        $existingIndexNames = $this->getIndexNames('inventories');
        $targetUniqueName = 'inventories_user_id_equipment_name_unique';

        if (!in_array($targetUniqueName, $existingIndexNames, true)) {
            Schema::table('inventories', function (Blueprint $table) use ($targetUniqueName) {
                $table->unique(['user_id', 'equipment_name'], $targetUniqueName);
            });
        }

        // 3) Optional: pastikan ada index user_id biasa (untuk query filter per user)
        $existingIndexNames = $this->getIndexNames('inventories');
        $userIndexName = 'inventories_user_id_index';

        if (!in_array($userIndexName, $existingIndexNames, true)) {
            Schema::table('inventories', function (Blueprint $table) use ($userIndexName) {
                $table->index('user_id', $userIndexName);
            });
        }
    }

    public function down(): void
    {
        // rollback: hapus unique per user, lalu balikin unique global equipment_name (opsional)
        $existingIndexNames = $this->getIndexNames('inventories');

        $targetUniqueName = 'inventories_user_id_equipment_name_unique';
        if (in_array($targetUniqueName, $existingIndexNames, true)) {
            Schema::table('inventories', function (Blueprint $table) use ($targetUniqueName) {
                $table->dropUnique($targetUniqueName);
            });
        }

        // Balikin unique global (kalau kamu memang mau revert ke versi lama)
        // Kalau gak mau, boleh hapus block ini.
        $existingIndexNames = $this->getIndexNames('inventories');
        $oldUniqueName = 'inventories_equipment_name_unique';

        if (!in_array($oldUniqueName, $existingIndexNames, true)) {
            Schema::table('inventories', function (Blueprint $table) use ($oldUniqueName) {
                $table->unique('equipment_name', $oldUniqueName);
            });
        }
    }
};