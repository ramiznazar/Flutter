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
        Schema::create('giveaway', function (Blueprint $table) {
            $table->id();
            $table->text('icon')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giveaway');
    }
};
