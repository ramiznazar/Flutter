<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * Inserts migration records for any "create_*_table" migration whose table already exists.
 * Use when your DB has tables that were created outside migrate (e.g. import/restore),
 * so "php artisan migrate" no longer fails with "Table already exists".
 */
class SyncMigrationsForExistingTables extends Command
{
    protected $signature = 'db:sync-migrations-existing-tables 
                            {--dry-run : Only show what would be marked}';

    protected $description = 'Mark as run any migration whose table already exists (fixes "Table already exists" on migrate)';

    public function handle(): int
    {
        $driver = Config::get('database.default');
        if ($driver !== 'mysql') {
            $this->error('This command only supports MySQL.');
            return self::FAILURE;
        }

        $db = Config::get('database.connections.mysql.database');
        try {
            $ran = DB::table('migrations')->pluck('migration')->flip()->all();
            $batch = (int) DB::table('migrations')->max('batch') + 1;
        } catch (\Throwable $e) {
            $this->error('Could not read migrations table. Run: php artisan migrate:install');
            return self::FAILURE;
        }
        if ($batch < 1) {
            $batch = 1;
        }

        // App migrations + known package paths (no Migrator dependency)
        $paths = array_filter([
            database_path('migrations'),
            base_path('vendor/laravel/sanctum/database/migrations'),
        ], 'is_dir');
        $files = [];
        foreach ($paths as $path) {
            foreach (glob($path . '/*.php') ?: [] as $f) {
                $files[] = $f;
            }
        }
        $files = array_unique($files);
        sort($files);

        $marked = 0;
        foreach ($files as $f) {
            $name = basename($f, '.php');
            if (isset($ran[$name])) {
                continue;
            }
            $table = $this->tableFromCreateMigration($name, $f);
            if ($table === null) {
                continue;
            }
            if (!$this->tableExists($db, $table)) {
                continue;
            }
            if ($this->option('dry-run')) {
                $this->line("Would mark as run: {$name} (table `{$table}` exists)");
                $marked++;
                continue;
            }
            DB::table('migrations')->insert(['migration' => $name, 'batch' => $batch]);
            $this->line("Marked as run: {$name} (table `{$table}` exists)");
            $marked++;
        }

        if ($marked === 0) {
            $this->info('No migrations needed to be marked (all in sync or no existing tables without records).');
        } elseif ($this->option('dry-run')) {
            $this->info("Dry run: {$marked} migration(s) would be marked. Run without --dry-run to apply.");
        } else {
            $this->info("Marked {$marked} migration(s). You can run: php artisan migrate --force");
        }

        return self::SUCCESS;
    }

    private function tableExists(string $db, string $table): bool
    {
        $r = DB::selectOne(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1",
            [$db, $table]
        );
        return $r !== null;
    }

    /**
     * If $name looks like *create_*_table (e.g. 2019_08_19_000000_create_failed_jobs_table), get table from Schema::create('...').
     */
    private function tableFromCreateMigration(string $name, string $path): ?string
    {
        if (!preg_match('/create_.+_table$/', $name)) {
            return null;
        }
        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }
        if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $m)) {
            return $m[1];
        }
        return null;
    }
}
