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
        Schema::create('spin_cailmed', function (Blueprint $table) {
            $table->integer('UserID')->unique()->nullable();
            $table->integer('Total')->nullable();
            $table->text('EndAt')->nullable();
            $table->text('StartedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_cailmed');
    }
};
