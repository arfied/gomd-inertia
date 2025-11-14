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
        // Add referring_agent_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('referring_agent_id')->nullable()->after('remember_token');
            $table->foreign('referring_agent_id')->references('id')->on('agents')->onDelete('set null');
        });

        // Add referring_agent_id to businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedBigInteger('referring_agent_id')->nullable()->after('zip');
            $table->foreign('referring_agent_id')->references('id')->on('agents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove referring_agent_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referring_agent_id']);
            $table->dropColumn('referring_agent_id');
        });

        // Remove referring_agent_id from businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['referring_agent_id']);
            $table->dropColumn('referring_agent_id');
        });
    }
};
