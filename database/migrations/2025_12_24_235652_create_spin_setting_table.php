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
        Schema::create('spin_setting', function (Blueprint $table) {
            $table->integer('ID')->autoIncrement();
            $table->boolean('ShowAd')->nullable();
            $table->text('AdType')->nullable();
            $table->integer('MaxLimit')->nullable();
            $table->text('Time')->nullable();
            $table->boolean('SpinShow')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_setting');
    }
};
