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
        Schema::create('spin', function (Blueprint $table) {
            $table->integer('ID')->autoIncrement();
            $table->text('Prize')->nullable();
            $table->text('Type')->nullable();
            $table->text('Color')->nullable();
            $table->text('CreatedAt')->nullable();
            $table->boolean('Status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin');
    }
};
