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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('badge_name', 255)->nullable();
            $table->integer('mining_sessions_required')->nullable();
            $table->integer('spin_wheel_required')->nullable();
            $table->integer('invite_friends_required')->nullable();
            $table->integer('crutox_in_wallet_required')->nullable();
            $table->boolean('social_media_task_completed')->nullable();
            $table->text('badges_icon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
