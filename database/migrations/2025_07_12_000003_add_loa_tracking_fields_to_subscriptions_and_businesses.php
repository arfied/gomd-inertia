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
        // Add LOA tracking fields to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('loa_user_id')->nullable()->after('agent_id')->constrained('users')->onDelete('set null');
            $table->string('enrollment_source')->nullable()->after('loa_user_id');
            $table->index('loa_user_id');
            $table->index('enrollment_source');
        });

        // Add LOA tracking fields to businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('loa_user_id')->nullable()->after('referring_agent_id')->constrained('users')->onDelete('set null');
            $table->string('enrollment_source')->nullable()->after('loa_user_id');
            $table->index('loa_user_id');
            $table->index('enrollment_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove LOA tracking fields from subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['loa_user_id']);
            $table->dropIndex(['loa_user_id']);
            $table->dropIndex(['enrollment_source']);
            $table->dropColumn(['loa_user_id', 'enrollment_source']);
        });

        // Remove LOA tracking fields from businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['loa_user_id']);
            $table->dropIndex(['loa_user_id']);
            $table->dropIndex(['enrollment_source']);
            $table->dropColumn(['loa_user_id', 'enrollment_source']);
        });
    }
};
