<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signup_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('signup_uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('signup_path'); // medication_first, condition_first, plan_first
            $table->string('medication_id')->nullable();
            $table->string('condition_id')->nullable();
            $table->string('plan_id')->nullable();
            $table->json('questionnaire_responses')->nullable();
            $table->string('payment_id')->nullable();
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payment_status')->nullable(); // success, pending, failed
            $table->string('subscription_id')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('failure_reason')->nullable(); // validation_error, payment_failed, system_error
            $table->text('failure_message')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('user_id');
            $table->index('signup_path');
            $table->index('status');
            $table->index('plan_id');
            $table->index('medication_id');
            $table->index('condition_id');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signup_read_model');
    }
};

