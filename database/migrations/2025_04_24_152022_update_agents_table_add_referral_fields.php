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
        Schema::table('agents', function (Blueprint $table) {
            // Drop existing agent_level and agent_type columns
            $table->dropColumn(['agent_level', 'agent_type']);

            // Add new tier column with specific agent tiers
            $table->enum('tier', ['SFMO', 'FMO', 'SVG', 'MGA', 'AGENT'])->after('experience');

            // Add referring agent relationship
            $table->foreignId('referring_agent_id')->nullable()->after('user_id')
                  ->references('id')->on('agents')->onDelete('set null');

            // Update commission_rate to be tier-based
            $table->decimal('commission_rate', 5, 2)->default(30.00)->change();

            // Add referral token for secure links
            $table->string('referral_token')->nullable()->unique()->after('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            // Restore original columns
            $table->string('agent_level')->default('field_agent');
            $table->string('agent_type');

            // Remove added columns
            $table->dropForeign(['referring_agent_id']);
            $table->dropColumn(['referring_agent_id', 'tier', 'referral_token']);
        });
    }
};
