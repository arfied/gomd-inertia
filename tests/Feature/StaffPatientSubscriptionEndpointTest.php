<?php

use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Str;

it('requires authentication and staff role for staff subscription renewal', function () {
    $patientUuid = (string) Str::uuid();

    $this->postJson(route('patients.subscription.renew', ['patientUuid' => $patientUuid]))
        ->assertStatus(401);

    /** @var User $user */
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->postJson(route('patients.subscription.renew', ['patientUuid' => $patientUuid]))
        ->assertStatus(403);
});

it('returns 404 when patient not found', function () {
    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $this->actingAs($staff);

    $patientUuid = (string) Str::uuid();

    $response = $this->postJson(route('patients.subscription.renew', ['patientUuid' => $patientUuid]));

    $response->assertStatus(404);
});

it('returns error when patient has no subscription', function () {
    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.subscription.renew', ['patientUuid' => $patientUuid]));

    $response
        ->assertStatus(404)
        ->assertJson([
            'error' => 'No active subscription found for this patient',
        ]);
});

it('staff can renew a patient subscription', function () {
    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $originalEndsAt = now()->addMonth();
    $subscription = Subscription::create([
        'user_id' => $patient->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => $originalEndsAt,
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.subscription.renew', ['patientUuid' => $patientUuid]));

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

it('staff can renew a cancelled patient subscription and reactivate it', function () {
    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $originalEndsAt = now()->subDay();
    $subscription = Subscription::create([
        'user_id' => $patient->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subMonth(),
        'ends_at' => $originalEndsAt,
        'status' => Subscription::STATUS_CANCELLED,
        'is_trial' => false,
        'cancelled_at' => now(),
    ]);

    $this->actingAs($staff);

    $response = $this->postJson(route('patients.subscription.renew', ['patientUuid' => $patientUuid]));

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

