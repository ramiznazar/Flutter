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
        Schema::create('shop_views', function (Blueprint $table) {
            $table->integer('ID')->autoIncrement();
            $table->integer('Shop_ID')->nullable();
            $table->integer('User_ID')->nullable();
            $table->text('CreatedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_views');
    }
};
