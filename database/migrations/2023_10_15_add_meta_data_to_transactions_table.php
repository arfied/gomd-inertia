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
        // Only run this migration if the transactions table exists
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Check if the column doesn't already exist
                if (!Schema::hasColumn('transactions', 'meta_data')) {
                    $table->json('meta_data')->nullable()->after('error_message')->comment('Additional data for the transaction');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run this migration if the transactions table exists
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Check if the column exists before trying to drop it
                if (Schema::hasColumn('transactions', 'meta_data')) {
                    $table->dropColumn('meta_data');
                }
            });
        }
    }
};
