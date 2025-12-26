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
        Schema::create('user_boosters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('booster_type', 50)->default('2x')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(1)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('is_active');
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_boosters');
    }
};
