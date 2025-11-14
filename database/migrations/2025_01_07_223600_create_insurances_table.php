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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_name');
            $table->string('policy_number');
            $table->string('group_number')->nullable();
            $table->string('plan_type');  // HMO, PPO, EPO, etc.
            $table->date('coverage_start_date')->nullable();
            $table->date('coverage_end_date')->nullable();
            $table->decimal('copay_amount', 10, 2)->nullable();
            $table->decimal('deductible_amount', 10, 2)->nullable();
            $table->decimal('coverage_limit', 10, 2)->nullable();
            $table->boolean('prior_authorization_required')->default(false);
            $table->string('formulary_type')->nullable();  // open, closed, etc.
            $table->string('status');  // active, expired, cancelled
            $table->timestamps();

            $table->unique(['user_id', 'policy_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
