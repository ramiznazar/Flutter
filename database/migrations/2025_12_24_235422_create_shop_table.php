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
        Schema::create('shop', function (Blueprint $table) {
            $table->integer('ID')->autoIncrement();
            $table->text('Image')->nullable();
            $table->text('Title')->nullable();
            $table->text('Link')->nullable();
            $table->text('Likes')->nullable();
            $table->tinyInteger('isliked')->nullable();
            $table->tinyInteger('Status')->nullable();
            $table->text('CreatedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop');
    }
};
