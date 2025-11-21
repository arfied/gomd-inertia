<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_payment_methods(): void
    {
        PaymentMethod::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/patient/payment-methods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'is_default', 'verification_status', 'display_name']
                ],
                'count'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_credit_card(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'credit_card',
                'cc_last_four' => '4242',
                'cc_brand' => 'Visa',
                'cc_expiration_month' => '12',
                'cc_expiration_year' => '2025',
                'cc_token' => 'token_123',
                'is_default' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'credit_card')
            ->assertJsonPath('data.is_default', true)
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $this->user->id,
            'type' => 'credit_card',
            'cc_last_four' => '4242',
        ]);
    }

    public function test_user_can_create_ach_account(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'ach',
                'ach_account_name' => 'John Doe',
                'ach_account_type' => 'checking',
                'ach_routing_number_last_four' => '0001',
                'ach_account_number_last_four' => '5678',
                'ach_token' => 'ach_token_123',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'ach')
            ->assertJsonPath('data.verification_status', 'pending');
    }

    public function test_user_can_create_invoice_method(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'invoice',
                'invoice_company_name' => 'Acme Corp',
                'invoice_contact_name' => 'John Doe',
                'invoice_email' => 'billing@acme.com',
                'invoice_phone' => '555-1234',
                'invoice_billing_address' => '123 Main St',
                'invoice_payment_terms' => 'net_30',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'invoice')
            ->assertJsonPath('data.verification_status', 'verified');
    }

    public function test_user_can_view_payment_method(): void
    {
        $paymentMethod = PaymentMethod::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/patient/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $paymentMethod->id);
    }

    public function test_user_cannot_view_other_users_payment_method(): void
    {
        $otherUser = User::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/patient/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_set_payment_method_as_default(): void
    {
        $pm1 = PaymentMethod::factory()->create(['user_id' => $this->user->id, 'is_default' => true]);
        $pm2 = PaymentMethod::factory()->create(['user_id' => $this->user->id, 'is_default' => false]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/patient/payment-methods/{$pm2->id}/set-default");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_default', true);

        $this->assertFalse($pm1->fresh()->is_default);
        $this->assertTrue($pm2->fresh()->is_default);
    }

    public function test_user_cannot_delete_default_payment_method(): void
    {
        $paymentMethod = PaymentMethod::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/patient/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Cannot delete the default payment method. Please set another as default first.');
    }

    public function test_user_can_delete_non_default_payment_method(): void
    {
        $paymentMethod = PaymentMethod::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/patient/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(204);

        $this->assertNotNull($paymentMethod->fresh()->archived_at);
    }

    public function test_first_payment_method_is_set_as_default(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'credit_card',
                'cc_last_four' => '4242',
                'cc_brand' => 'Visa',
                'cc_expiration_month' => '12',
                'cc_expiration_year' => '2025',
                'cc_token' => 'token_123',
                'is_default' => false,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_default', true);
    }

    public function test_validation_fails_for_invalid_credit_card(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'credit_card',
                'cc_last_four' => '42',  // Invalid: should be 4 digits
                'cc_brand' => 'Visa',
                'cc_expiration_month' => '12',
                'cc_expiration_year' => '2025',
                'cc_token' => 'token_123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('cc_last_four');
    }
}

