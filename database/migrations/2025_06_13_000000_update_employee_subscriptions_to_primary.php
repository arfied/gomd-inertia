<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing employee subscriptions to be primary accounts
        DB::table('subscriptions')
            ->where('user_type', 'business_employee')
            ->whereNull('is_primary_account')
            ->orWhere('is_primary_account', false)
            ->update([
                'is_primary_account' => true,
                'family_role' => 'primary',
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert employee subscriptions back to non-primary
        DB::table('subscriptions')
            ->where('user_type', 'business_employee')
            ->update([
                'is_primary_account' => false,
                'family_role' => null,
                'updated_at' => now()
            ]);
    }
};
