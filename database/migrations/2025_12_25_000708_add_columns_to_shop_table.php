<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $t = 'shop';
        Schema::table($t, function (Blueprint $table) use ($t) {
            if (!Schema::hasColumn($t, 'Description')) {
                $table->text('Description')->nullable()->after('Title');
            }
            if (!Schema::hasColumn($t, 'Price')) {
                $table->decimal('Price', 10, 2)->default(0)->nullable()->after('Description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop', function (Blueprint $table) {
            $table->dropColumn(['Description', 'Price']);
        });
    }
};
