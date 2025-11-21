<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.subscription.show'));

    $response->assertStatus(401);
});

it('returns null subscription when the user has no subscription', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->getJson(route('patient.subscription.show'));

    $response
        ->assertOk()
        ->assertJson([
            'subscription' => null,
        ]);
});

it('returns the current subscription for the authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('patient.subscription.show'));

    $response
        ->assertOk()
        ->assertJson([
            'subscription' => [
                'id' => $subscription->id,
                'status' => Subscription::STATUS_ACTIVE,
                'plan_name' => 'TeleMed Pro Monthly',
                'is_trial' => false,
            ],
        ])
        ->assertJsonStructure([
            'subscription' => [
                'id',
                'status',
                'plan_name',
                'is_trial',
                'starts_at',
                'ends_at',
            ],
        ]);
});

it('requires authentication to cancel a subscription', function () {
    $response = $this->postJson(route('patient.subscription.cancel'));

    $response->assertStatus(401);
});

it('returns null subscription when cancelling with no current subscription', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('patient.subscription.cancel'));

    $response
        ->assertOk()
        ->assertJson([
            'subscription' => null,
        ]);
});

it('cancels the current subscription for the authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('patient.subscription.cancel'));

    $response
        ->assertOk()
        ->assertJson([
            'subscription' => [
                'id' => $subscription->id,
                'status' => Subscription::STATUS_CANCELLED,
            ],
        ]);

    $subscription->refresh();

    expect($subscription->status)->toBe(Subscription::STATUS_CANCELLED);
    expect($subscription->cancelled_at)->not->toBeNull();
});

it('requires authentication to renew a subscription', function () {
    $response = $this->postJson(route('patient.subscription.renew'));

    $response->assertStatus(401);
});

it('returns error when renewing with no current subscription', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('patient.subscription.renew'));

    $response
        ->assertStatus(404)
        ->assertJson([
            'error' => 'No active subscription found',
        ]);
});

it('renews the current subscription for the authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $originalEndsAt = now()->addMonth();
    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => $originalEndsAt,
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('patient.subscription.renew'));

    $response
        ->assertOk()
        ->assertJson([
            'subscription' => [
                'id' => $subscription->id,
                'status' => Subscription::STATUS_ACTIVE,
                'plan_name' => 'TeleMed Pro Monthly',
            ],
        ]);

    $subscription->refresh();

    // Verify the end date was extended by the plan duration
    expect($subscription->ends_at->toDateString())
        ->toBe($originalEndsAt->addMonth()->toDateString());
});

it('can renew a cancelled subscription and reactivate it', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $originalEndsAt = now()->subDay();
    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subMonth(),
        'ends_at' => $originalEndsAt,
        'status' => Subscription::STATUS_CANCELLED,
        'is_trial' => false,
        'cancelled_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('patient.subscription.renew'));

    $response
        ->assertOk()
        ->assertJson([
            'subscription' => [
                'id' => $subscription->id,
                'status' => Subscription::STATUS_ACTIVE,
                'plan_name' => 'TeleMed Pro Monthly',
            ],
        ]);

    $subscription->refresh();

    // Verify the subscription is now active
    expect($subscription->status)->toBe(Subscription::STATUS_ACTIVE);
    // Verify cancelled_at is cleared
    expect($subscription->cancelled_at)->toBeNull();
    // Verify the end date was extended by the plan duration
    expect($subscription->ends_at->toDateString())
        ->toBe($originalEndsAt->addMonth()->toDateString());
});

it('rate limits renewal requests per hour', function () {
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($user);

    // Make 5 successful renewal requests
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson(route('patient.subscription.renew'));
        expect($response->status())->toBeLessThan(429);
    }

    // 6th request should be rate limited
    $response = $this->postJson(route('patient.subscription.renew'));
    expect($response->status())->toBe(429);
    expect($response->json('error'))->toContain('Too many renewal attempts');
});

