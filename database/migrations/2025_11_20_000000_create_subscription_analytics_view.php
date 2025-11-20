<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_analytics_view', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->string('plan_name')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->enum('status', ['active', 'cancelled', 'expired', 'paused'])->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->integer('months_active')->default(0);
            $table->string('churn_reason')->nullable();
            $table->boolean('is_trial')->default(false);
            $table->timestamp('last_payment_date')->nullable();
            $table->timestamp('next_payment_date')->nullable();
            $table->timestamps();

            // Indexes for analytics queries
            $table->index(['status', 'started_at']);
            $table->index(['status', 'cancelled_at']);
            $table->index(['plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_analytics_view');
    }
};

