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
        Schema::create('agent_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->integer('commission_count');
            $table->string('payout_method')->default('bank_transfer');
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reference_number')->unique();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'status']);
            $table->index('reference_number');
            $table->index('processed_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_payouts');
    }
};
