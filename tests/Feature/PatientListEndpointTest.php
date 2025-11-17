<?php

use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Allergy;
use App\Models\MedicalRecord;
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
                    'has_documents',
                    'has_medical_history',
                    'has_active_subscription',
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

it('includes metadata flags for documents, medical history, and active subscription in the patient list', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $patientWithAll = User::factory()->create([
        'fname' => 'Alice',
        'lname' => 'Example',
        'email' => 'alice@example.test',
    ]);
    $patientWithAll->role = 'patient';

    $patientWithout = User::factory()->create([
        'fname' => 'Bob',
        'lname' => 'Other',
        'email' => 'bob@example.test',
    ]);
    $patientWithout->role = 'patient';

    foreach ([$patientWithAll, $patientWithout] as $user) {
        PatientEnrollment::create([
            'patient_uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'source' => 'manual',
            'metadata' => [],
            'enrolled_at' => now(),
        ]);
    }

    $plan = SubscriptionPlan::create([
        'name' => 'Flag Test Plan',
        'price' => 100,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    Subscription::create([
        'user_id' => $patientWithAll->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    MedicalRecord::create([
        'patient_id' => $patientWithAll->id,
        'doctor_id' => null,
        'record_type' => 'lab',
        'description' => 'Test document',
        'record_date' => now()->toDateString(),
        'file_path' => 'test/path.pdf',
    ]);

    Allergy::create([
        'user_id' => $patientWithAll->id,
        'allergen' => 'Peanuts',
        'reaction' => 'Anaphylaxis',
        'severity' => 'severe',
        'notes' => null,
    ]);

    $this->actingAs($staff);

    $json = $this->getJson(route('patients.index', ['per_page' => 10]))
        ->assertOk()
        ->json();

    $alice = null;
    $bob = null;

    foreach ($json['patients'] as $row) {
        if ($row['email'] === 'alice@example.test') {
            $alice = $row;
        }
        if ($row['email'] === 'bob@example.test') {
            $bob = $row;
        }
    }

    expect($alice)->not->toBeNull();
    expect($bob)->not->toBeNull();

    expect($alice['has_documents'])->toBeTrue();
    expect($alice['has_medical_history'])->toBeTrue();
    expect($alice['has_active_subscription'])->toBeTrue();

    expect($bob['has_documents'])->toBeFalse();
    expect($bob['has_medical_history'])->toBeFalse();
    expect($bob['has_active_subscription'])->toBeFalse();
});

it('supports filtering patient list and count by metadata flags', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $withAll = User::factory()->create([
        'email' => 'with.all@example.test',
    ]);
    $withAll->role = 'patient';

    $withDocumentsOnly = User::factory()->create([
        'email' => 'with.docs@example.test',
    ]);
    $withDocumentsOnly->role = 'patient';

    $withoutAny = User::factory()->create([
        'email' => 'without.any@example.test',
    ]);
    $withoutAny->role = 'patient';

    foreach ([$withAll, $withDocumentsOnly, $withoutAny] as $user) {
        PatientEnrollment::create([
            'patient_uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'source' => 'manual',
            'metadata' => [],
            'enrolled_at' => now(),
        ]);
    }

    $plan = SubscriptionPlan::create([
        'name' => 'Filter Test Plan',
        'price' => 100,
        'duration_months' => 1,
        'service_limit' => null,
        'status' => 'active',
    ]);

    Subscription::create([
        'user_id' => $withAll->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'status' => Subscription::STATUS_ACTIVE,
        'is_trial' => false,
    ]);

    foreach ([$withAll, $withDocumentsOnly] as $user) {
        MedicalRecord::create([
            'patient_id' => $user->id,
            'doctor_id' => null,
            'record_type' => 'lab',
            'description' => 'Test document',
            'record_date' => now()->toDateString(),
            'file_path' => 'test/path.pdf',
        ]);
    }

    Allergy::create([
        'user_id' => $withAll->id,
        'allergen' => 'Dust',
        'reaction' => 'Sneezing',
        'severity' => 'mild',
        'notes' => null,
    ]);

    $this->actingAs($staff);

    // has_documents filter
    $response = $this->getJson(route('patients.index', [
        'has_documents' => 1,
        'per_page' => 10,
    ]));

    $response->assertOk();
    $emails = collect($response->json('patients'))->pluck('email')->all();

    expect($emails)->toContain('with.all@example.test');
    expect($emails)->toContain('with.docs@example.test');
    expect($emails)->not->toContain('without.any@example.test');

    $this->getJson(route('patients.count', ['has_documents' => 1]))
        ->assertOk()
        ->assertJson([
            'count' => 2,
        ]);

    // has_medical_history filter
    $response = $this->getJson(route('patients.index', [
        'has_medical_history' => 1,
        'per_page' => 10,
    ]));

    $response->assertOk();
    $emails = collect($response->json('patients'))->pluck('email')->all();

    expect($emails)->toContain('with.all@example.test');
    expect($emails)->not->toContain('with.docs@example.test');
    expect($emails)->not->toContain('without.any@example.test');

    $this->getJson(route('patients.count', ['has_medical_history' => 1]))
        ->assertOk()
        ->assertJson([
            'count' => 1,
        ]);

    // has_active_subscription filter
    $response = $this->getJson(route('patients.index', [
        'has_active_subscription' => 1,
        'per_page' => 10,
    ]));

    $response->assertOk();
    $emails = collect($response->json('patients'))->pluck('email')->all();

    expect($emails)->toContain('with.all@example.test');
    expect($emails)->not->toContain('with.docs@example.test');
    expect($emails)->not->toContain('without.any@example.test');

    $this->getJson(route('patients.count', ['has_active_subscription' => 1]))
        ->assertOk()
        ->assertJson([
            'count' => 1,
        ]);
});


