<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure settings.id is AUTO_INCREMENT and PRIMARY KEY so inserts without explicit id work.
     * Fixes "Field 'id' doesn't have a default value" when table came from dump.
     * MySQL requires auto column to be a key, so we set PRIMARY KEY in the same MODIFY.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        $db = config('database.connections.mysql.database');
        $prefix = config('database.connections.mysql.prefix', '');
        $table = $prefix . 'settings';
        $r = DB::selectOne(
            "SELECT EXTRA, COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id'",
            [$db, $table]
        );
        if (!$r || str_contains((string) $r->EXTRA, 'auto_increment')) {
            return;
        }
        try {
            $pk = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'PRIMARY KEY'", [$db, $table]);
            if ($pk) {
                // PK exists; only add AUTO_INCREMENT (id must already be the key)
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            } else {
                // No PK; set both so AUTO_INCREMENT is allowed
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
            }
        } catch (\Throwable $e) {
            // Skip on any error so migrate can continue
        }
    }

    public function down(): void
    {
        // Irreversible without knowing previous definition
    }
};
