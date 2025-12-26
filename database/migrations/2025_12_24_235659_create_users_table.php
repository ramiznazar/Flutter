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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('country')->nullable();
            $table->text('password')->nullable();
            $table->text('token')->nullable();
            $table->text('coin')->nullable();
            $table->text('is_mining')->nullable();
            $table->text('mining_end_time')->nullable();
            $table->text('coin_end_time')->nullable();
            $table->text('total_coin_claim')->nullable();
            $table->text('last_active')->nullable();
            $table->text('mining_time')->nullable();
            $table->text('username')->nullable();
            $table->text('username_count')->nullable();
            $table->integer('total_invite')->nullable();
            $table->text('invite_setup')->nullable();
            $table->text('account_status')->nullable();
            $table->text('ban_reason')->nullable();
            $table->text('ban_date')->nullable();
            $table->text('otp')->nullable();
            $table->text('join_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
