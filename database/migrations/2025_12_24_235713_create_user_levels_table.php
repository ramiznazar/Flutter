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
        Schema::create('user_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('mining_session')->nullable();
            $table->integer('spin_wheel')->nullable();
            $table->integer('current_level')->nullable();
            $table->text('achieved_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('current_level')->references('id')->on('level')->onDelete('cascade')->onUpdate('cascade');
            $table->index('user_id');
            $table->index('current_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
