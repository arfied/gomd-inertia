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
        Schema::table('payment_methods', function (Blueprint $table) {
            // Add verification status for ACH accounts
            $table->string('verification_status')->default('pending')->after('is_default');
            
            // Track verification attempts
            $table->integer('verification_attempts')->default(0)->after('verification_status');
            
            // Track last verification attempt
            $table->timestamp('last_verification_attempt_at')->nullable()->after('verification_attempts');
            
            // Soft delete support (archived_at instead of deleted_at for clarity)
            $table->timestamp('archived_at')->nullable()->after('updated_at');
            
            // Add indexes for common queries
            $table->index(['user_id', 'verification_status']);
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'verification_status']);
            $table->dropIndex(['user_id', 'is_default']);
            $table->dropColumn([
                'verification_status',
                'verification_attempts',
                'last_verification_attempt_at',
                'archived_at',
            ]);
        });
    }
};

