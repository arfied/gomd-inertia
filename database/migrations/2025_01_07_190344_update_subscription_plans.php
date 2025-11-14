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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('type')->nullable()->after('duration_months');
            $table->json('features')->nullable()->after('type');
            $table->json('benefits')->nullable()->after('features');
            $table->boolean('insurance_eligible')->default(false)->after('benefits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['type', 'features', 'benefits', 'insurance_eligible']);
        });
    }
};
