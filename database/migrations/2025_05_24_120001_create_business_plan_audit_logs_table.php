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
        Schema::create('business_plan_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // User who made the change
            $table->foreignId('business_employee_id')->nullable()->constrained()->nullOnDelete(); // Employee if self-payment
            
            // Action details
            $table->string('action'); // 'self_payment_completed', 'quantity_decreased', 'total_price_decreased', etc.
            $table->string('description');
            
            // Change tracking
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Additional context
            $table->string('source')->default('system'); // 'system', 'admin', 'employee_self_payment'
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            // Reference to related records
            $table->foreignId('self_payment_id')->nullable()->constrained('business_plan_self_payments')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['business_plan_id', 'action']);
            $table->index(['user_id', 'action']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_plan_audit_logs');
    }
};
