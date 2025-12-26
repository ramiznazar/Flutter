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
        Schema::create('user_guide', function (Blueprint $table) {
            $table->unsignedBigInteger('userID')->nullable();
            $table->boolean('home')->default(1)->nullable();
            $table->tinyInteger('mining')->default(1)->nullable();
            $table->boolean('wallet')->default(1)->nullable();
            $table->boolean('badges')->default(1)->nullable();
            $table->boolean('level')->default(1)->nullable();
            $table->boolean('teamProfile')->default(1)->nullable();
            $table->boolean('news')->default(1)->nullable();
            $table->boolean('shop')->default(1)->nullable();
            $table->boolean('userProfile')->default(1)->nullable();
            $table->foreign('userID')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_guide');
    }
};
