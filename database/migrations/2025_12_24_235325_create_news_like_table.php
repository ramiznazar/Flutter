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
        Schema::create('news_like', function (Blueprint $table) {
            $table->integer('ID')->autoIncrement();
            $table->integer('News_ID')->nullable();
            $table->integer('User_ID')->nullable();
            $table->text('CreatedAt')->nullable();
            $table->foreign('News_ID')->references('ID')->on('news')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_like');
    }
};
