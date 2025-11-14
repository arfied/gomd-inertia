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
        Schema::table('conditions', function (Blueprint $table) {
            if (!Schema::hasColumn('conditions', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conditions', function (Blueprint $table) {
            if (Schema::hasColumn('conditions', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
