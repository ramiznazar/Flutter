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
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('task_id')->nullable();
            $table->enum('task_type', ['daily', 'onetime'])->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('reward_available_at')->nullable();
            $table->boolean('reward_claimed')->default(0)->nullable();
            $table->dateTime('reward_claimed_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('task_id')->references('ID')->on('social_media_setting')->onDelete('cascade')->onUpdate('cascade');
            $table->index('user_id');
            $table->index('task_id');
            $table->index('task_type');
            $table->index('reward_available_at');
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};
