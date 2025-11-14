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
            $table->foreignId('referring_loa_id')->nullable()->after('referring_agent_id')->constrained('users')->onDelete('set null');
            $table->index('referring_loa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referring_loa_id']);
            $table->dropIndex(['referring_loa_id']);
            $table->dropColumn('referring_loa_id');
        });
    }
};
