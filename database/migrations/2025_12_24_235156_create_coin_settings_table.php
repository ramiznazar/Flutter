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
        Schema::create('coin_settings', function (Blueprint $table) {
            $table->id();
            $table->text('seconds_per_coin')->nullable();
            $table->text('max_seconds_allow')->nullable();
            $table->text('claim_time_in_sec')->nullable();
            $table->text('max_coin_claim_allow')->nullable();
            $table->text('token')->nullable();
            $table->text('token_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_settings');
    }
};
