<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure giveaway.id is AUTO_INCREMENT and PRIMARY KEY so inserts without
     * explicit id work when table came from dump.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        $db = config('database.connections.mysql.database');
        $prefix = config('database.connections.mysql.prefix', '');
        $table = $prefix . 'giveaway';
        $r = DB::selectOne(
            "SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id'",
            [$db, $table]
        );
        if (!$r || str_contains((string) $r->EXTRA, 'auto_increment')) {
            return;
        }
        try {
            $pk = DB::selectOne("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'PRIMARY KEY'", [$db, $table]);
            if ($pk) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` INT NOT NULL AUTO_INCREMENT");
            } else {
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
            }
        } catch (\Throwable $e) {
            // Skip if already correct or structure differs
        }
    }

    public function down(): void
    {
        // Irreversible
    }
};
