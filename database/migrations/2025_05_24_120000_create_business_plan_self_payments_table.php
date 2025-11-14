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
        Schema::create('business_plan_self_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            
            // Payment details
            $table->integer('amount_paid')->comment('Amount paid in cents (should be 2000 for $20)');
            $table->string('payment_method')->default('credit_card');
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            
            // Tracking fields
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->json('meta_data')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['business_plan_id', 'status']);
            $table->index(['business_employee_id', 'status']);
            $table->index(['user_id', 'status']);
            
            // Unique constraint to prevent duplicate self-payments
            $table->unique(['business_plan_id', 'business_employee_id'], 'unique_employee_self_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_plan_self_payments');
    }
};
