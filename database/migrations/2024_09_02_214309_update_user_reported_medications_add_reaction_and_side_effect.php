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
        Schema::table('user_reported_medications', function (Blueprint $table) {
            $table->string('reaction')->nullable()->after('frequency');
            $table->string('side_effects')->nullable()->after('reaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_reported_medications', function (Blueprint $table) {
            $table->dropColumn('reaction', 'side_effects');
        });
    }
};
