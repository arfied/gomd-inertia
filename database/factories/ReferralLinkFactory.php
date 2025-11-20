<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\ReferralLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReferralLink>
 */
class ReferralLinkFactory extends Factory
{
    protected $model = ReferralLink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'referral_type' => $this->faker->randomElement(['patient', 'agent', 'business']),
            'referral_code' => strtoupper(Str::random(8)),
            'referral_token' => Str::uuid(),
            'clicks_count' => $this->faker->numberBetween(0, 100),
            'conversions_count' => $this->faker->numberBetween(0, 50),
            'conversion_rate' => $this->faker->randomFloat(2, 0, 100),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the referral link is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the referral link is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}

