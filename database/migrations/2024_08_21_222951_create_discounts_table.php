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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'percentage' or 'fixed'
            $table->decimal('value', 8, 2);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_subscription_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_subscription_plan');
        Schema::dropIfExists('discounts');
    }
};
