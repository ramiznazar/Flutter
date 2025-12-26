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
        Schema::create('mystery_box_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('box_type', ['common', 'rare', 'epic', 'legendary'])->nullable();
            $table->integer('ads_watched')->default(0)->nullable();
            $table->integer('ads_required')->nullable();
            $table->dateTime('last_ad_watched_at')->nullable();
            $table->dateTime('cooldown_until')->nullable();
            $table->boolean('box_opened')->default(0)->nullable();
            $table->decimal('reward_coins', 10, 2)->nullable();
            $table->dateTime('opened_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->index('user_id');
            $table->index('box_type');
            $table->index('cooldown_until');
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mystery_box_claims');
    }
};
