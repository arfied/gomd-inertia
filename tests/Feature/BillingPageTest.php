<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_access_billing_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/billing');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_billing_page(): void
    {
        $response = $this->get('/billing');

        $response->assertRedirect('/login');
    }

    public function test_billing_page_displays_payment_methods(): void
    {
        PaymentMethod::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/patient/payment-methods');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_add_credit_card_from_billing_page(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'credit_card',
                'cc_last_four' => '4242',
                'cc_brand' => 'Visa',
                'cc_expiration_month' => '12',
                'cc_expiration_year' => '2025',
                'cc_token' => 'token_123',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'credit_card')
            ->assertJsonPath('data.verification_status', 'verified');
    }

    public function test_user_can_set_default_payment_method(): void
    {
        $method = PaymentMethod::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/patient/payment-methods/{$method->id}/set-default");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_default', true);
    }

    public function test_user_cannot_delete_default_payment_method(): void
    {
        $method = PaymentMethod::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/patient/payment-methods/{$method->id}");

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Cannot delete the default payment method. Please set another as default first.');
    }

    public function test_user_can_delete_non_default_payment_method(): void
    {
        $method = PaymentMethod::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/patient/payment-methods/{$method->id}");

        $response->assertStatus(204);

        $deletedMethod = PaymentMethod::withTrashed()->find($method->id);
        $this->assertNotNull($deletedMethod->archived_at);
    }

    public function test_user_can_update_payment_method(): void
    {
        $method = PaymentMethod::factory()->creditCard()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/patient/payment-methods/{$method->id}", [
                'type' => 'credit_card',
                'cc_last_four' => '4242',
                'cc_brand' => 'Visa',
                'cc_expiration_month' => '06',
                'cc_expiration_year' => '2026',
                'cc_token' => 'token_123',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.cc_expiration_month', '06');
    }

    public function test_user_cannot_access_other_users_payment_methods(): void
    {
        $otherUser = User::factory()->create();
        $method = PaymentMethod::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/patient/payment-methods/{$method->id}");

        $response->assertStatus(403);
    }

    public function test_first_payment_method_is_automatically_default(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/patient/payment-methods', [
                'type' => 'credit_card',
                'cc_last_four' => '4242',
                'cc_brand' => 'Visa',
                'cc_expiration_month' => '12',
                'cc_expiration_year' => '2025',
                'cc_token' => 'token_123',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_default', true);
    }
}

