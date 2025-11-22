<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Plan',
            'price' => $this->faker->randomFloat(2, 10, 100),
            'duration_months' => $this->faker->randomElement([1, 3, 6, 12]),
            'service_limit' => $this->faker->randomElement([null, 5, 10, 20, 50]),
            'status' => 'active',
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'show_free_trial' => $this->faker->boolean(50),
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
                'status' => 'inactive',
            ];
        });
    }

    /**
     * Indicate that the plan is featured.
     */
    public function featured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_featured' => true,
            ];
        });
    }

    /**
     * Indicate that the plan has unlimited service.
     */
    public function unlimited(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'service_limit' => null,
            ];
        });
    }
}

