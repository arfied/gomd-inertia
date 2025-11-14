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
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            // Add treatment_id and subscription_plan_id columns
            $table->foreignId('treatment_id')->nullable()->after('user_id');
            $table->foreignId('subscription_plan_id')->nullable()->after('treatment_id');

            // Add status column
            $table->string('status')->default('pending')->after('family_weight_history');

            // Add transaction_id column
            $table->string('transaction_id')->nullable()->after('status');

            // Add amount column
            $table->decimal('amount', 10, 2)->nullable()->after('transaction_id');

            // Add data column for storing JSON data
            $table->json('data')->nullable()->after('amount');

            // Make cart_session_id nullable
            $table->string('cart_session_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            // Drop the columns we added
            $table->dropColumn([
                'treatment_id',
                'subscription_plan_id',
                'status',
                'transaction_id',
                'amount',
                'data'
            ]);

            // Make cart_session_id required again
            $table->string('cart_session_id')->nullable(false)->change();
        });
    }
};
