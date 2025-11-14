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
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();

            // The agent who earned the commission
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');

            // The referring agent who gets a cut (if applicable)
            $table->foreignId('upline_agent_id')->nullable()->constrained('agents')->onDelete('set null');

            // The transaction that generated this commission
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');

            // The subscription that generated this commission
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');

            // Commission amounts
            $table->decimal('total_amount', 10, 2); // Total transaction amount
            $table->decimal('commission_amount', 10, 2); // Amount earned by agent
            $table->decimal('upline_commission_amount', 10, 2)->default(0.00); // Amount earned by upline

            // Commission rates used for this transaction
            $table->decimal('agent_rate', 5, 2); // Rate for the agent
            $table->decimal('upline_rate', 5, 2)->default(0.00); // Rate for the upline

            // Status of the commission
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');

            // Payment dates
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};
