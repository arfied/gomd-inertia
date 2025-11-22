<?php

namespace Tests\Feature\Admin;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FailedRenewalsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create();
    }

    public function test_admin_can_view_failed_renewals(): void
    {
        // Create a failed renewal event
        $subscription = Subscription::factory()->create(['user_id' => $this->regularUser->id]);

        DB::table('event_store')->insert([
            'aggregate_uuid' => 'test-saga-uuid',
            'aggregate_type' => 'subscription_renewal_saga',
            'event_type' => 'SubscriptionRenewalSagaFailed',
            'event_data' => json_encode([
                'subscription_id' => $subscription->id,
                'user_id' => $this->regularUser->id,
                'amount' => 99.99,
                'reason' => 'payment_declined',
                'error_message' => 'Card declined',
                'attempts_made' => 3,
            ]),
            'metadata' => json_encode([]),
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/failed-renewals');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total_failures', 1)
            ->assertJsonPath('summary.total_amount', 99.99)
            ->assertJsonPath('data.0.reason', 'payment_declined');
    }

    public function test_non_admin_cannot_view_failed_renewals(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/admin/failed-renewals');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_failed_renewals(): void
    {
        $response = $this->getJson('/admin/failed-renewals');

        $response->assertStatus(401);
    }

    public function test_failed_renewals_filtered_by_days(): void
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->regularUser->id]);

        // Create old failure (15 days ago)
        DB::table('event_store')->insert([
            'aggregate_uuid' => 'old-saga-uuid',
            'aggregate_type' => 'subscription_renewal_saga',
            'event_type' => 'SubscriptionRenewalSagaFailed',
            'event_data' => json_encode([
                'subscription_id' => $subscription->id,
                'user_id' => $this->regularUser->id,
                'amount' => 50.00,
                'reason' => 'payment_declined',
            ]),
            'metadata' => json_encode([]),
            'occurred_at' => now()->subDays(15),
        ]);

        // Create recent failure (2 days ago)
        DB::table('event_store')->insert([
            'aggregate_uuid' => 'recent-saga-uuid',
            'aggregate_type' => 'subscription_renewal_saga',
            'event_type' => 'SubscriptionRenewalSagaFailed',
            'event_data' => json_encode([
                'subscription_id' => $subscription->id,
                'user_id' => $this->regularUser->id,
                'amount' => 99.99,
                'reason' => 'payment_declined',
            ]),
            'metadata' => json_encode([]),
            'occurred_at' => now()->subDays(2),
        ]);

        // Query last 7 days
        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/failed-renewals?days=7');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total_failures', 1)
            ->assertJsonPath('data.0.amount', 99.99);
    }

    public function test_admin_can_view_single_failed_renewal(): void
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->regularUser->id]);

        DB::table('event_store')->insert([
            'aggregate_uuid' => 'test-saga-uuid',
            'aggregate_type' => 'subscription_renewal_saga',
            'event_type' => 'SubscriptionRenewalSagaFailed',
            'event_data' => json_encode([
                'subscription_id' => $subscription->id,
                'user_id' => $this->regularUser->id,
                'amount' => 99.99,
                'reason' => 'payment_declined',
                'error_message' => 'Card declined',
                'attempts_made' => 3,
            ]),
            'metadata' => json_encode([]),
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/failed-renewals/test-saga-uuid');

        $response->assertStatus(200)
            ->assertJsonPath('saga_uuid', 'test-saga-uuid')
            ->assertJsonPath('amount', 99.99)
            ->assertJsonPath('reason', 'payment_declined');
    }

    public function test_view_nonexistent_failed_renewal_returns_404(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/failed-renewals/nonexistent-uuid');

        $response->assertStatus(404);
    }
}

