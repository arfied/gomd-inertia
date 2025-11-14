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
        Schema::table('businesses', function (Blueprint $table) {
            // Add missing fields that are used in the enrollment controller
            $table->string('owner_fname')->nullable()->after('zip');
            $table->string('owner_lname')->nullable()->after('owner_fname');
            $table->foreignId('admin_user_id')->nullable()->after('owner_lname')->constrained('users')->onDelete('set null');
            $table->foreignId('agent_id')->nullable()->after('admin_user_id')->constrained('agents')->onDelete('set null');
            $table->string('status')->default('active')->after('agent_id');
            
            // Add indexes for better performance
            $table->index('admin_user_id');
            $table->index('agent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['admin_user_id']);
            $table->dropForeign(['agent_id']);
            
            // Drop indexes
            $table->dropIndex(['admin_user_id']);
            $table->dropIndex(['agent_id']);
            $table->dropIndex(['status']);
            
            // Drop columns
            $table->dropColumn(['owner_fname', 'owner_lname', 'admin_user_id', 'agent_id', 'status']);
        });
    }
};
