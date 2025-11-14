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
        Schema::create('offline_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('gclid')->nullable();
            $table->string('conversion_name');
            $table->decimal('conversion_value', 10, 2);
            $table->string('conversion_currency')->default('USD');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_conversions');
    }
};
