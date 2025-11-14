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
        // Only run this migration if the subscriptions table exists
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                // Check if the column doesn't already exist
                if (!Schema::hasColumn('subscriptions', 'click_id')) {
                    // Check if the afid column exists before using it as a reference
                    if (Schema::hasColumn('subscriptions', 'afid')) {
                        $table->string('click_id')->nullable()->after('afid')->comment('LinkTrust ClickID for recurring billing');
                    } else {
                        $table->string('click_id')->nullable()->comment('LinkTrust ClickID for recurring billing');
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run this migration if the subscriptions table exists
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                // Check if the column exists before trying to drop it
                if (Schema::hasColumn('subscriptions', 'click_id')) {
                    $table->dropColumn('click_id');
                }
            });
        }
    }
};
