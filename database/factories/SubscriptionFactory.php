<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 year', 'now');
        $endsAt = (clone $startsAt)->modify('+1 month');

        return [
            'user_id' => User::factory(),
            'plan_id' => SubscriptionPlan::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => Subscription::STATUS_ACTIVE,
            'is_trial' => false,
            'is_primary_account' => true,
            'family_role' => Subscription::FAMILY_ROLE_PRIMARY,
            'meta_data' => [],
        ];
    }

    /**
     * Indicate that the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Subscription::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Subscription::STATUS_EXPIRED,
                'ends_at' => now()->subDay(),
            ];
        });
    }

    /**
     * Indicate that the subscription is pending payment.
     */
    public function pendingPayment(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Subscription::STATUS_PENDING_PAYMENT,
            ];
        });
    }

    /**
     * Indicate that the subscription is a trial.
     */
    public function trial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_trial' => true,
            ];
        });
    }

    /**
     * Indicate that the subscription is a dependent account.
     */
    public function dependent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary_account' => false,
                'primary_subscription_id' => Subscription::factory(),
                'family_role' => $this->faker->randomElement([
                    Subscription::FAMILY_ROLE_DEPENDENT_ADULT,
                    Subscription::FAMILY_ROLE_DEPENDENT_MINOR,
                    Subscription::FAMILY_ROLE_DEPENDENT_OLDER,
                ]),
            ];
        });
    }
}

