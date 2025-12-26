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
        Schema::create('ads_setting', function (Blueprint $table) {
            $table->id();
            $table->text('applovin_sdk_key')->nullable();
            $table->text('applovin_inter_id')->nullable();
            $table->text('applovin_reward_id')->nullable();
            $table->text('applovin_native_id')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_setting');
    }
};
