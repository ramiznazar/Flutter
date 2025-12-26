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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('update_version')->nullable();
            $table->text('maintenance')->nullable();
            $table->text('force_update')->nullable();
            $table->text('update_message')->nullable();
            $table->text('maintenance_message')->nullable();
            $table->text('update_link')->nullable();
            $table->text('pirvacy_policy_link')->nullable();
            $table->text('term_n_condition_link')->nullable();
            $table->text('support_email')->nullable();
            $table->text('faq_link')->nullable();
            $table->text('white_paper_link')->nullable();
            $table->text('road_map_link')->nullable();
            $table->text('about_us_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
