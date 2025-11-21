<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['credit_card', 'ach', 'invoice']);

        $data = [
            'user_id' => User::factory(),
            'type' => $type,
            'is_default' => false,
            'verification_status' => $type === 'ach' ? 'pending' : 'verified',
            'verification_attempts' => 0,
        ];

        if ($type === 'credit_card') {
            $data = array_merge($data, [
                'cc_last_four' => $this->faker->numerify('####'),
                'cc_brand' => $this->faker->randomElement(['Visa', 'Mastercard', 'American Express', 'Discover']),
                'cc_expiration_month' => $this->faker->numberBetween(1, 12),
                'cc_expiration_year' => $this->faker->numberBetween(2025, 2035),
                'cc_token' => 'token_' . $this->faker->uuid(),
            ]);
        } elseif ($type === 'ach') {
            $data = array_merge($data, [
                'ach_account_name' => $this->faker->name(),
                'ach_account_type' => $this->faker->randomElement(['checking', 'savings']),
                'ach_routing_number_last_four' => $this->faker->numerify('####'),
                'ach_account_number_last_four' => $this->faker->numerify('####'),
                'ach_token' => 'ach_token_' . $this->faker->uuid(),
            ]);
        } elseif ($type === 'invoice') {
            $data = array_merge($data, [
                'invoice_company_name' => $this->faker->company(),
                'invoice_contact_name' => $this->faker->name(),
                'invoice_email' => $this->faker->companyEmail(),
                'invoice_phone' => $this->faker->phoneNumber(),
                'invoice_billing_address' => $this->faker->address(),
                'invoice_payment_terms' => $this->faker->randomElement(['net_15', 'net_30', 'net_45', 'net_60']),
            ]);
        }

        return $data;
    }

    /**
     * Indicate that the payment method is a credit card.
     */
    public function creditCard(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'credit_card',
                'verification_status' => 'verified',
                'cc_last_four' => '4242',
                'cc_brand' => 'Visa',
                'cc_expiration_month' => 12,
                'cc_expiration_year' => 2025,
                'cc_token' => 'token_' . $this->faker->uuid(),
            ];
        });
    }

    /**
     * Indicate that the payment method is an ACH account.
     */
    public function ach(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'ach',
                'verification_status' => 'pending',
                'ach_account_name' => $this->faker->name(),
                'ach_account_type' => 'checking',
                'ach_routing_number_last_four' => '0001',
                'ach_account_number_last_four' => '5678',
                'ach_token' => 'ach_token_' . $this->faker->uuid(),
            ];
        });
    }

    /**
     * Indicate that the payment method is an invoice.
     */
    public function invoice(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'invoice',
                'verification_status' => 'verified',
                'invoice_company_name' => $this->faker->company(),
                'invoice_contact_name' => $this->faker->name(),
                'invoice_email' => $this->faker->companyEmail(),
                'invoice_phone' => $this->faker->phoneNumber(),
                'invoice_billing_address' => $this->faker->address(),
                'invoice_payment_terms' => 'net_30',
            ];
        });
    }

    /**
     * Indicate that the payment method is the default.
     */
    public function default(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
            ];
        });
    }

    /**
     * Indicate that the payment method is verified.
     */
    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'verification_status' => 'verified',
            ];
        });
    }
}

