<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgentCommission>
 */
class AgentCommissionFactory extends Factory
{
    protected $model = AgentCommission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commissionAmount = $this->faker->randomFloat(2, 10, 500);

        return [
            'agent_id' => Agent::factory(),
            'upline_agent_id' => null,
            'transaction_id' => Transaction::factory(),
            'subscription_id' => null,
            'total_amount' => $commissionAmount * 2,
            'commission_amount' => $commissionAmount,
            'upline_commission_amount' => 0.00,
            'agent_rate' => 30.00,
            'upline_rate' => 0.00,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Set the commission status to paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Set the commission status to cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Set the upline agent.
     */
    public function withUplineAgent(Agent $uplineAgent): static
    {
        return $this->state(fn (array $attributes) => [
            'upline_agent_id' => $uplineAgent->id,
        ]);
    }
}

