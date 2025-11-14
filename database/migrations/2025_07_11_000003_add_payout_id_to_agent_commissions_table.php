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
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->foreignId('payout_id')->nullable()->after('paid_at')->constrained('agent_payouts')->onDelete('set null');
            $table->index('payout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->dropForeign(['payout_id']);
            $table->dropIndex(['payout_id']);
            $table->dropColumn('payout_id');
        });
    }
};
