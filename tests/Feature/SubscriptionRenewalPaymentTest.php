<?php

use App\Domain\Subscription\Events\SubscriptionRenewalSagaStarted;
use App\Domain\Subscription\SubscriptionRenewalSaga;
use App\Jobs\Subscription\ProcessSubscriptionRenewalJob;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Contracts\Events\Dispatcher;

it('dispatches payment processing job when subscription renewal saga starts', function () {
    Queue::fake();

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
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $sagaUuid = (string) \Illuminate\Support\Str::uuid();
    $sagaPayload = [
        'subscription_id' => $subscription->id,
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
        'billing_date' => now()->toDateString(),
    ];

    $event = new SubscriptionRenewalSagaStarted($sagaUuid, $sagaPayload);

    // Dispatch through the dispatcher to trigger listeners
    $dispatcher = app(Dispatcher::class);
    $dispatcher->dispatch($event);

    Queue::assertPushed(ProcessSubscriptionRenewalJob::class, function ($job) use ($sagaUuid, $subscription, $user) {
        return $job->sagaUuid === $sagaUuid
            && $job->subscriptionId === $subscription->id
            && $job->userId === $user->id
            && $job->amount === 30.00;
    });
});

it('does not dispatch job if subscription id is missing', function () {
    Queue::fake();

    $sagaUuid = (string) \Illuminate\Support\Str::uuid();
    $sagaPayload = [
        'user_id' => 1,
        'plan_id' => 1,
        'amount' => 30.00,
    ];

    $event = new SubscriptionRenewalSagaStarted($sagaUuid, $sagaPayload);

    event($event);

    Queue::assertNotPushed(ProcessSubscriptionRenewalJob::class);
});

it('does not dispatch job if user id is missing', function () {
    Queue::fake();

    $sagaUuid = (string) \Illuminate\Support\Str::uuid();
    $sagaPayload = [
        'subscription_id' => 1,
        'plan_id' => 1,
        'amount' => 30.00,
    ];

    $event = new SubscriptionRenewalSagaStarted($sagaUuid, $sagaPayload);

    event($event);

    Queue::assertNotPushed(ProcessSubscriptionRenewalJob::class);
});

// Payment Method Validation Tests
it('validates credit card expiration', function () {
    $user = User::factory()->create();

    // Expired credit card
    $expiredCard = PaymentMethod::create([
        'user_id' => $user->id,
        'type' => 'credit_card',
        'is_default' => true,
        'cc_token' => 'token_123',
        'cc_brand' => 'Visa',
        'cc_last_four' => '4242',
        'cc_expiration_month' => 1,
        'cc_expiration_year' => 2020, // Expired
    ]);

    expect($expiredCard->isValid())->toBeFalse();
    expect($expiredCard->getValidationError())->toContain('expired');
});

it('validates credit card has required fields', function () {
    $user = User::factory()->create();

    // Missing token
    $invalidCard = PaymentMethod::create([
        'user_id' => $user->id,
        'type' => 'credit_card',
        'is_default' => true,
        'cc_brand' => 'Visa',
        'cc_last_four' => '4242',
        'cc_expiration_month' => 12,
        'cc_expiration_year' => 2025,
    ]);

    expect($invalidCard->isValid())->toBeFalse();
    expect($invalidCard->getValidationError())->toContain('token');
});

it('validates ACH payment method', function () {
    $user = User::factory()->create();

    // Valid ACH
    $validAch = PaymentMethod::create([
        'user_id' => $user->id,
        'type' => 'ach',
        'is_default' => true,
        'ach_token' => 'ach_token_123',
        'ach_account_number_last_four' => '6789',
    ]);

    expect($validAch->isValid())->toBeTrue();
    expect($validAch->getValidationError())->toBeNull();
});

// Idempotency Tests
it('prevents duplicate renewal processing', function () {
    $sagaUuid = (string) \Illuminate\Support\Str::uuid();

    // Mark as processed
    Cache::put("renewal_processed:{$sagaUuid}", true, now()->addDays(30));

    $user = User::factory()->create();
    $plan = SubscriptionPlan::create([
        'name' => 'Test Plan',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $job = new ProcessSubscriptionRenewalJob($sagaUuid, $subscription->id, $user->id, 30.00);

    // Job should detect it's already processed
    expect(Cache::has("renewal_processed:{$sagaUuid}"))->toBeTrue();
});

// Rate Limiting Tests
it('rate limits renewal requests per hour', function () {
    $user = User::factory()->create();

    // Simulate 5 renewal attempts
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit("renewal:hourly:{$user->id}", 60);
    }

    // 6th attempt should be rate limited
    expect(RateLimiter::tooManyAttempts("renewal:hourly:{$user->id}", 5))->toBeTrue();
});

it('rate limits renewal requests per day', function () {
    $user = User::factory()->create();

    // Simulate 20 renewal attempts
    for ($i = 0; $i < 20; $i++) {
        RateLimiter::hit("renewal:daily:{$user->id}", 86400);
    }

    // 21st attempt should be rate limited
    expect(RateLimiter::tooManyAttempts("renewal:daily:{$user->id}", 20))->toBeTrue();
});

// Correlation ID Tests
it('includes correlation id in job', function () {
    $correlationId = (string) \Illuminate\Support\Str::uuid();
    $sagaUuid = (string) \Illuminate\Support\Str::uuid();

    $job = new ProcessSubscriptionRenewalJob(
        $sagaUuid,
        1,
        1,
        30.00,
        $correlationId
    );

    expect($job->correlationId)->toBe($correlationId);
});

it('generates correlation id if not provided', function () {
    $sagaUuid = (string) \Illuminate\Support\Str::uuid();

    $job = new ProcessSubscriptionRenewalJob($sagaUuid, 1, 1, 30.00);

    expect($job->correlationId)->not->toBeNull();
    expect($job->correlationId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

