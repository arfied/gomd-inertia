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
        Schema::table('discounts', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->integer('priority')->default(0)->after('status');
            $table->integer('max_uses')->nullable()->after('priority');
            $table->integer('current_uses')->default(0)->after('max_uses');
            $table->index(['status', 'start_date', 'end_date'], 'idx_discount_status_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['description', 'priority', 'max_uses', 'current_uses']);
            $table->dropIndex('idx_discount_status_dates');
        });
    }
};
