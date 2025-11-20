<?php

namespace Database\Factories;

use App\Models\ReferralClick;
use App\Models\ReferralLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReferralClick>
 */
class ReferralClickFactory extends Factory
{
    protected $model = ReferralClick::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referral_link_id' => ReferralLink::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referrer_url' => $this->faker->url(),
            'session_id' => $this->faker->uuid(),
            'converted' => false,
            'converted_at' => null,
        ];
    }

    /**
     * Indicate that the click was converted.
     */
    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'converted' => true,
            'converted_at' => now(),
        ]);
    }
}

