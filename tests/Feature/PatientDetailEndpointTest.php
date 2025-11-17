<?php

use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Str;

it('returns unauthorized for guests', function () {
    $this->getJson(route('patients.show', ['patientUuid' => (string) Str::uuid()]))
        ->assertStatus(401);
});

it('forbids non-staff users from accessing patient detail endpoint', function () {
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->getJson(route('patients.show', ['patientUuid' => (string) Str::uuid()]))
        ->assertStatus(403);
});

it('returns 404 when the patient enrollment cannot be found', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $this->actingAs($staff);

    $this->getJson(route('patients.show', ['patientUuid' => (string) Str::uuid()]))
        ->assertStatus(404);
});

it('returns patient detail for staff users', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create([
        'fname' => 'Alice',
        'lname' => 'Wonder',
        'email' => 'alice@example.test',
        'gender' => 'female',
        'dob' => '1990-01-02',
        'address1' => '123 Main St',
        'city' => 'Metropolis',
        'state' => 'NY',
        'zip' => '10001',
        'phone' => '555-0000',
        'mobile_phone' => '555-0001',
    ]);
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    $enrollment = PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => ['foo' => 'bar'],
        'enrolled_at' => now()->subDay(),
    ]);

    $plan = SubscriptionPlan::create([
        'name' => 'TeleMed Pro Monthly',
        'price' => 30.00,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    $subscription = Subscription::create([
        'user_id' => $patient->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.show', ['patientUuid' => $patientUuid]));

    $response
        ->assertOk()
        ->assertJson([
            'patient' => [
                'patient_uuid' => $enrollment->patient_uuid,
                'user_id' => $patient->id,
                'fname' => 'Alice',
                'lname' => 'Wonder',
                'email' => 'alice@example.test',
                'enrollment' => [
                    'source' => 'manual',
                    'metadata' => ['foo' => 'bar'],
                ],
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'plan_name' => 'TeleMed Pro Monthly',
                    'is_trial' => false,
                ],
            ],
        ])
        ->assertJsonStructure([
            'patient' => [
                'patient_uuid',
                'user_id',
                'fname',
                'lname',
                'email',
                'status',
                'demographics' => [
                    'gender',
                    'dob',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'phone',
                    'mobile_phone',
                ],
                'enrollment' => [
                    'source',
                    'metadata',
                    'enrolled_at',
                ],
                'subscription' => [
                    'id',
                    'status',
                    'plan_name',
                    'is_trial',
                    'starts_at',
                    'ends_at',
                ],
            ],
        ]);
});

