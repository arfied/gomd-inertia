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
        Schema::table('business_plans', function (Blueprint $table) {
            $table->string('click_id')->nullable()->after('discount_percent')->comment('LinkTrust ClickID for tracking');
            $table->string('afid')->nullable()->after('click_id')->comment('LinkTrust AffiliateID for recurring billing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_plans', function (Blueprint $table) {
            $table->dropColumn(['click_id', 'afid']);
        });
    }
};
