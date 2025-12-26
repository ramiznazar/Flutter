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
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->string('didit_request_id', 255)->nullable()->after('admin_notes');
            $table->string('didit_status', 50)->nullable()->after('didit_request_id');
            $table->text('didit_verification_data')->nullable()->after('didit_status');
            $table->dateTime('didit_verified_at')->nullable()->after('didit_verification_data');
            $table->index('didit_request_id');
            $table->index('didit_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->dropIndex(['didit_request_id', 'didit_status']);
            $table->dropColumn(['didit_request_id', 'didit_status', 'didit_verification_data', 'didit_verified_at']);
        });
    }
};
