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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('managing_agent_id')->nullable()->after('referring_agent_id')->constrained('agents')->onDelete('set null');
            $table->index('managing_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['managing_agent_id']);
            $table->dropIndex(['managing_agent_id']);
            $table->dropColumn('managing_agent_id');
        });
    }
};
