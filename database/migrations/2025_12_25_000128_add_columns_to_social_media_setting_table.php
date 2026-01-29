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
        $t = 'social_media_setting';
        Schema::table($t, function (Blueprint $table) use ($t) {
            if (!Schema::hasColumn($t, 'task_type')) {
                $table->string('task_type', 50)->default('onetime')->nullable()->after('Token');
            }
            if (!Schema::hasColumn($t, 'Status')) {
                $table->boolean('Status')->default(1)->nullable()->after('task_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_setting', function (Blueprint $table) {
            $table->dropColumn(['task_type', 'Status']);
        });
    }
};
