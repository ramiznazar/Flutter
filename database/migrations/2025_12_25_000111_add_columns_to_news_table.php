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
     * Note: Link column is optional and matches PHP behavior where it's added dynamically.
     * This allows SQL backup imports to work without Link column, and PHP code will add it if needed.
     */
    public function up(): void
    {
        // Only add Link column if it doesn't exist (to support SQL backup imports)
        if (Schema::hasTable('news') && !Schema::hasColumn('news', 'Link')) {
            Schema::table('news', function (Blueprint $table) {
                $table->text('Link')->nullable()->default(null)->after('Description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('news') && Schema::hasColumn('news', 'Link')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn('Link');
            });
        }
    }
};
