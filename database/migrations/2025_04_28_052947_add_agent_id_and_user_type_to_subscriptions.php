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
            $table->unsignedBigInteger('agent_id')->nullable()->after('user_id');
            $table->string('user_type')->nullable()->after('agent_id');

            // Add indexes for better performance
            $table->index('agent_id');
            $table->index('user_type');

            // Add foreign key constraint
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['agent_id']);

            // Drop indexes
            $table->dropIndex(['agent_id']);
            $table->dropIndex(['user_type']);

            // Drop columns
            $table->dropColumn('agent_id');
            $table->dropColumn('user_type');
        });
    }
};
