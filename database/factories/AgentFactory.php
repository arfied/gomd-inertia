<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company' => $this->faker->name(),
            'experience' => $this->faker->randomElement(['new', '1-3', '3-5', '5+']),
            'status' => 'approved',
            'tier' => $this->faker->randomElement(['AGENT', 'MGA', 'SVG', 'FMO', 'SFMO']),
            'commission_rate' => 30.00,
            'referral_code' => strtoupper(Str::random(8)),
            'referral_token' => Str::uuid(),
            'npn' => $this->faker->numerify('##########'),
        ];
    }

    /**
     * Set the agent tier.
     */
    public function tier(string $tier): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => $tier,
        ]);
    }

    /**
     * Set the referring agent.
     */
    public function referredBy(Agent $referrer): static
    {
        return $this->state(fn (array $attributes) => [
            'referring_agent_id' => $referrer->id,
        ]);
    }

    /**
     * Create an agent with pending status.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create an agent with approved status.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }
}

