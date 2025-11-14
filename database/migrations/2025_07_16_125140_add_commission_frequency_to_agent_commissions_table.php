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
            $table->enum('commission_frequency', ['monthly', 'biannual', 'annual'])
                  ->default('monthly')
                  ->after('upline_rate')
                  ->comment('Commission frequency based on subscription plan duration');

            $table->index('commission_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->dropIndex(['commission_frequency']);
            $table->dropColumn('commission_frequency');
        });
    }
};
