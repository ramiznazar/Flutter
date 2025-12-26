<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration ensures the users.id column has AUTO_INCREMENT attribute.
     * This is necessary if the table was created from a SQL import that didn't
     * properly set AUTO_INCREMENT, or if MySQL strict mode requires it.
     */
    public function up(): void
    {
        // Check if the column already has AUTO_INCREMENT
        $columnInfo = DB::select("SHOW COLUMNS FROM `users` WHERE Field = 'id'");
        
        if (!empty($columnInfo)) {
            $column = (array) $columnInfo[0];
            $extra = strtolower($column['Extra'] ?? '');
            $type = $column['Type'] ?? '';
            $null = $column['Null'] ?? 'NO';
            $key = $column['Key'] ?? '';
            
            // Only modify if AUTO_INCREMENT is not already set
            if (strpos($extra, 'auto_increment') === false) {
                // Check if id is already a primary key
                $isPrimaryKey = strtoupper($key) === 'PRI';
                
                if (!$isPrimaryKey) {
                    // Check if there's an existing primary key and drop it
                    $indexes = DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'PRIMARY'");
                    if (!empty($indexes)) {
                        DB::statement("ALTER TABLE `users` DROP PRIMARY KEY");
                    }
                    // Make id the primary key
                    DB::statement("ALTER TABLE `users` ADD PRIMARY KEY (`id`)");
                }
                
                // Now modify the column to add AUTO_INCREMENT
                // Preserve the existing column type
                $nullClause = strtoupper($null) === 'YES' ? 'NULL' : 'NOT NULL';
                DB::statement("ALTER TABLE `users` MODIFY COLUMN `id` {$type} {$nullClause} AUTO_INCREMENT");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We can't really reverse this without knowing the previous state
        // In practice, you would need to know what the original column definition was
    }
};
