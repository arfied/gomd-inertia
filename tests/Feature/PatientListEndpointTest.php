<?php

use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Str;

it('returns unauthorized for guests', function () {
    $this->getJson(route('patients.index'))->assertStatus(401);
    $this->getJson(route('patients.count'))->assertStatus(401);
});

it('forbids non-staff users from accessing patient list endpoints', function () {
    $patient = User::factory()->create();
    $patient->role = 'patient';

    $this->actingAs($patient);

    $this->getJson(route('patients.index'))->assertStatus(403);
    $this->getJson(route('patients.count'))->assertStatus(403);
});

it('returns a simple paginated patient list for staff users', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patients = User::factory()->count(3)->create();

    foreach ($patients as $user) {
        $user->role = 'patient';

        PatientEnrollment::create([
            'patient_uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'source' => 'manual',
            'metadata' => [],
            'enrolled_at' => now(),
        ]);
    }

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.index', ['per_page' => 2]));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'patients' => [
                [
                    'patient_uuid',
                    'user_id',
                    'fname',
                    'lname',
                    'email',
                    'status',
                    'enrolled_at',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'next_page_url',
                'prev_page_url',
            ],
        ]);

    $json = $response->json();

    expect(count($json['patients']))->toBe(2);
    expect($json['meta'])->not->toHaveKeys(['total', 'last_page']);
});

it('returns a filtered count for the patient list', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $matching = User::factory()->create([
        'fname' => 'Jane',
        'lname' => 'Doe',
        'email' => 'jane@example.test',
    ]);
    $matching->role = 'patient';

    $other = User::factory()->create([
        'fname' => 'John',
        'lname' => 'Smith',
        'email' => 'john@example.test',
    ]);
    $other->role = 'patient';

    foreach ([$matching, $other] as $user) {
        PatientEnrollment::create([
            'patient_uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'source' => 'manual',
            'metadata' => [],
            'enrolled_at' => now(),
        ]);
    }

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.count', ['search' => 'jane']));

    $response->assertOk()->assertJson([
        'count' => 1,
    ]);
});

it('includes subscription summary data in the patient list when available', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patient = User::factory()->create([
        'fname' => 'Alice',
        'lname' => 'Example',
        'email' => 'alice@example.test',
    ]);
    $patient->role = 'patient';

    PatientEnrollment::create([
        'patient_uuid' => (string) Str::uuid(),
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => [],
        'enrolled_at' => now(),
    ]);

    $plan = SubscriptionPlan::create([
        'name' => 'Test Plan',
        'price' => 100,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    Subscription::create([
        'user_id' => $patient->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => true,
    ]);

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.index', ['per_page' => 10]));

    $response->assertOk();

    $json = $response->json();

    expect($json['patients'][0]['subscription'])->toMatchArray([
        'status' => Subscription::STATUS_ACTIVE,
        'plan_name' => 'Test Plan',
        'is_trial' => true,
    ]);
});

