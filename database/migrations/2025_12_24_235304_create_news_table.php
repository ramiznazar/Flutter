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
        Schema::create('news', function (Blueprint $table) {
            $table->integer('ID')->autoIncrement();
            $table->text('Image')->nullable();
            $table->text('Title')->nullable();
            $table->text('Description')->nullable();
            $table->text('CreatedAt')->nullable();
            $table->boolean('AdShow')->nullable();
            $table->boolean('RAdShow')->nullable();
            $table->text('Likes')->nullable();
            $table->boolean('isliked')->nullable();
            $table->integer('Status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
