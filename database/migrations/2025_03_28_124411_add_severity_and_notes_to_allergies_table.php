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
        Schema::table('allergies', function (Blueprint $table) {
            $table->string('severity')->nullable()->after('reaction');
            $table->text('notes')->nullable()->after('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allergies', function (Blueprint $table) {
            $table->dropColumn(['severity', 'notes']);
        });
    }
};
