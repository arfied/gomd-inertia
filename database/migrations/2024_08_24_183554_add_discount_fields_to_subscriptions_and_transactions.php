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
            $table->boolean('is_discounted')->default(false)->after('status');
            $table->decimal('discounted_price', 8, 2)->nullable()->after('is_discounted');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_discounted')->default(false)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_discounted');
            $table->dropColumn('discounted_price');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_discounted');
        });
    }
};
