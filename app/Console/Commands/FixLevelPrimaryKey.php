<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * Ensures the `level` table has a primary key on `id`.
 * Required when `level` was created from a dump that had no PRIMARY KEY,
 * so create_user_levels_table can add its foreign key to level(id).
 */
class FixLevelPrimaryKey extends Command
{
    protected $signature = 'db:fix-level-primary-key';
    protected $description = 'Add primary key to level(id) if missing (fixes user_levels FK error)';

    public function handle(): int
    {
        $driver = Config::get('database.default');
        if ($driver !== 'mysql') {
            $this->error('This command only supports MySQL.');
            return self::FAILURE;
        }

        $db = Config::get('database.connections.mysql.database');
        $prefix = Config::get('database.connections.mysql.prefix', '');
        $table = $prefix . 'level';

        $exists = DB::selectOne(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1",
            [$db, $table]
        );
        if (!$exists) {
            $this->warn("Table `{$table}` does not exist. Run migrations first.");
            return self::SUCCESS;
        }

        $hasPk = DB::selectOne(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = 'PRIMARY' LIMIT 1",
            [$db, $table]
        );
        if ($hasPk) {
            $this->info("Table `{$table}` already has a primary key.");
            return self::SUCCESS;
        }

        try {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
            $this->info("Added primary key to `{$table}`(id). You can run: php artisan migrate --force");
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
