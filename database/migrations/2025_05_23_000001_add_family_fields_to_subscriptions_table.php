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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('is_primary_account')->default(true)->after('is_trial');
            $table->unsignedBigInteger('primary_subscription_id')->nullable()->after('is_primary_account');
            $table->string('family_role', 20)->nullable()->after('primary_subscription_id');

            // Add foreign key constraint
            $table->foreign('primary_subscription_id')
                  ->references('id')
                  ->on('subscriptions')
                  ->onDelete('cascade');

            // Add index for better performance
            $table->index(['is_primary_account', 'primary_subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['primary_subscription_id']);

            // Drop index
            $table->dropIndex(['is_primary_account', 'primary_subscription_id']);

            // Drop columns
            $table->dropColumn(['is_primary_account', 'primary_subscription_id', 'family_role']);
        });
    }
};
