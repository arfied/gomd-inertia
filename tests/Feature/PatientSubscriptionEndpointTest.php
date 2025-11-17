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

