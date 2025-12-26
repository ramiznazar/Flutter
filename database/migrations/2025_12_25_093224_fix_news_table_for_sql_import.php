<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration ensures the news table matches the SQL backup structure exactly.
     * The Link column will be added dynamically by PHP code when needed (matching PHP behavior).
     * This allows SQL backup imports to work without modification.
     */
    public function up(): void
    {
        if (Schema::hasTable('news')) {
            // Remove Link column if it exists to match SQL backup structure
            // The PHP code will add it dynamically when needed (matching original PHP behavior)
            if (Schema::hasColumn('news', 'Link')) {
                Schema::table('news', function (Blueprint $table) {
                    $table->dropColumn('Link');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally add Link back if needed
        if (Schema::hasTable('news') && !Schema::hasColumn('news', 'Link')) {
            Schema::table('news', function (Blueprint $table) {
                $table->text('Link')->nullable()->default(null)->after('Description');
            });
        }
    }
};

