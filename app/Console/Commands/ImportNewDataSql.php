<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ImportNewDataSql extends Command
{
    protected $signature = 'db:import-new-data 
                            {--force : Skip confirmation}
                            {--setup : Run sync + fix-level + migrate first so all dump tables exist}
                            {--file= : Path to SQL file (default: new_data.sql in project root)}';

    protected $description = 'Clear DATA in tables that exist in the dump (TRUNCATE, do not drop), then import only INSERTs from new_data.sql. Keeps your current table structures. Use --setup if migrations are out of sync or tables are missing.';

    /**
     * Tables that have data in new_data.sql. We TRUNCATE these (keep structure), then load dump data.
     */
    protected array $tablesInDump = [
        'ads_setting',
        'badges',
        'coin_settings',
        'currency',
        'giveaway',
        'level',
        'news',
        'news_like',
        'settings',
        'shop',
        'shop_views',
        'social_media_setting',
        'social_media_tokens',
        'spin',
        'spin_cailmed',
        'spin_setting',
        'token_bonus_history',
        'users',
        'user_guide',
        'user_levels',
    ];

    private const STATE_SKIP = 'skip';
    private const STATE_CREATE = 'create';
    private const STATE_INSERT = 'insert';
    private const STATE_ALTER = 'alter';

    public function handle(): int
    {
        $file = $this->option('file') ?: base_path('new_data.sql');

        if (!is_file($file)) {
            $this->error("SQL file not found: {$file}");
            return self::FAILURE;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('This will TRUNCATE data in the listed tables (structures kept), then import only INSERT data from ' . basename($file) . '. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $driver = Config::get('database.default');
        if ($driver !== 'mysql') {
            $this->error('This command only supports MySQL. Current connection: ' . $driver);
            return self::FAILURE;
        }

        if ($this->option('setup')) {
            $this->info('Setup: syncing migrations for existing tables...');
            $this->call('db:sync-migrations-existing-tables');
            $this->info('Setup: ensuring level has primary key...');
            $this->call('db:fix-level-primary-key');
            $this->info('Setup: running migrations...');
            $this->call('migrate', ['--force' => true]);
            $this->newLine();
        }

        $prefix = Config::get('database.connections.mysql.prefix', '');

        $missing = [];
        foreach ($this->tablesInDump as $table) {
            $t = $prefix . $table;
            if (!$this->tableExists($t)) {
                $missing[] = $t;
            }
        }
        if ($missing !== []) {
            $this->error('These tables from the dump do not exist in the database:');
            foreach ($missing as $t) {
                $this->line('  - ' . $t);
            }
            $this->newLine();
            $this->line('Run with --setup to sync migrations and run migrate, then import:');
            $this->line('  php artisan db:import-new-data --force --setup');
            return self::FAILURE;
        }

        $this->info('Disabling foreign key checks and truncating data in tables from the dump (tables are kept)...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->tablesInDump as $table) {
            $t = $prefix . $table;
            try {
                DB::statement("TRUNCATE TABLE `{$t}`");
                $this->line("  Truncated: {$t}");
            } catch (\Throwable $e) {
                $this->warn("  Could not truncate {$t}: " . $e->getMessage());
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('Data cleared.');

        $this->info('Importing only INSERT data from ' . basename($file) . ' (CREATE/ALTER skipped)...');
        $ok = $this->streamInsertOnlyToMysql($file);
        if (!$ok) {
            return self::FAILURE;
        }

        $this->info('Import completed successfully.');
        $this->line('You can recreate the admin user with: php artisan db:seed --class=AdminSeeder');
        return self::SUCCESS;
    }

    private function tableExists(string $table): bool
    {
        $db = Config::get('database.connections.mysql.database');
        $r = DB::selectOne(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1",
            [$db, $table]
        );
        return $r !== null;
    }

    /**
     * Stream new_data.sql into mysql, writing only INSERT statements (and initial SET).
     * Skips CREATE TABLE and ALTER TABLE ... ADD CONSTRAINT to avoid FK/structure conflicts.
     */
    private function streamInsertOnlyToMysql(string $sqlPath): bool
    {
        $host = Config::get('database.connections.mysql.host', '127.0.0.1');
        $port = Config::get('database.connections.mysql.port', '3306');
        $user = Config::get('database.connections.mysql.username');
        $pass = Config::get('database.connections.mysql.password');
        $dbName = Config::get('database.connections.mysql.database');

        $args = [
            'mysql',
            '-h', $host,
            '-P', (string) $port,
            '-u', $user,
            $dbName,
        ];
        $env = getenv();
        if ($pass !== '' && $pass !== null) {
            $env['MYSQL_PWD'] = $pass;
        }
        $cmd = implode(' ', array_map('escapeshellarg', $args));
        $spec = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        $proc = proc_open($cmd, $spec, $pipes, null, $env);
        if (!is_resource($proc)) {
            $this->error('Could not start mysql process.');
            return false;
        }
        $stdin = $pipes[0];
        $stdout = $pipes[1];
        $stderr = $pipes[2];
        stream_set_blocking($stdout, false);
        stream_set_blocking($stderr, false);

        $header = "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n";
        fwrite($stdin, $header);

        $state = self::STATE_SKIP;
        $alterBuffer = '';
        $fp = fopen($sqlPath, 'r');
        if (!$fp) {
            $this->error('Could not open SQL file.');
            fclose($stdin);
            proc_close($proc);
            return false;
        }

        $pipeBroken = false;
        while (($line = fgets($fp)) !== false) {
            $trimmed = rtrim($line);

            if ($state === self::STATE_CREATE) {
                // CREATE ends with ");" or ") ENGINE=...;" etc.
                if (preg_match('/\)\s*;\s*$/', $trimmed)) {
                    $state = self::STATE_SKIP;
                }
                continue;
            }

            if ($state === self::STATE_ALTER) {
                $alterBuffer .= $line;
                if (str_contains($trimmed, ';')) {
                    $state = self::STATE_SKIP;
                    $alterBuffer = '';
                }
                continue;
            }

            if ($state === self::STATE_INSERT) {
                $n = @fwrite($stdin, $line);
                if ($n !== strlen($line)) {
                    $pipeBroken = true;
                    break;
                }
                if (preg_match('/\)\s*;\s*$/', $trimmed)) {
                    $state = self::STATE_SKIP;
                }
                continue;
            }

            // STATE_SKIP
            if (stripos($trimmed, 'CREATE TABLE') === 0) {
                $state = self::STATE_CREATE;
                continue;
            }
            if (stripos($trimmed, 'ALTER TABLE') !== false && stripos($trimmed, 'ADD CONSTRAINT') !== false) {
                $state = self::STATE_ALTER;
                $alterBuffer = $line;
                if (str_contains($trimmed, ';')) {
                    $state = self::STATE_SKIP;
                    $alterBuffer = '';
                }
                continue;
            }
            if (stripos($trimmed, 'INSERT INTO') === 0) {
                $state = self::STATE_INSERT;
                $n = @fwrite($stdin, $line);
                if ($n !== strlen($line)) {
                    $pipeBroken = true;
                    break;
                }
                if (preg_match('/\)\s*;\s*$/', $trimmed)) {
                    $state = self::STATE_SKIP;
                }
            }
        }

        fclose($fp);
        @fclose($stdin);

        $out = stream_get_contents($stdout);
        $err = stream_get_contents($stderr);
        fclose($stdout);
        fclose($stderr);
        $code = proc_close($proc);

        if ($pipeBroken || $code !== 0 || $err !== '') {
            $this->error('Import failed.');
            if ($err !== '') {
                $this->error(trim($err));
            }
            if ($pipeBroken && $err === '') {
                $this->error('MySQL exited early (e.g. missing table or syntax error). Ensure all dump tables exist; run: php artisan migrate --force');
            }
            if ($out !== '') {
                $this->line(trim($out));
            }
            return false;
        }
        return true;
    }
}
