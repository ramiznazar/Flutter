<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table structure matches new_data.sql for import compatibility.
     */
    public function up(): void
    {
        Schema::create('token_bonus_history', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('to_user_id');
            $table->integer('from_user_id');
            $table->text('amount');
            $table->text('expire_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_bonus_history');
    }
};
