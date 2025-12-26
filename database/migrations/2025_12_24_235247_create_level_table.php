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
        Schema::create('level', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->text('lvl_name')->nullable();
            $table->integer('mining_sessions')->nullable();
            $table->integer('spin_wheel')->nullable();
            $table->integer('total_invite')->nullable();
            $table->integer('user_account_old')->nullable();
            $table->text('perk_crutox_per_time')->nullable();
            $table->integer('perk_mining_time')->nullable();
            $table->text('perk_crutox_reward')->nullable();
            $table->text('perk_other_access')->nullable();
            $table->integer('is_ads_block')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level');
    }
};
