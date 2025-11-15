<?php

use App\Models\PatientEnrollment;
use App\Models\User;
use App\Models\StoredEvent;


it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.enrollment.show'));

    $response->assertStatus(401);
});

it('returns null enrollment when the user is not enrolled', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->getJson(route('patient.enrollment.show'));

    $response
        ->assertOk()
        ->assertJson([
            'enrollment' => null,
        ]);
});

it('returns enrollment data for the authenticated user when it exists', function () {
    $user = User::factory()->create();

    PatientEnrollment::create([
        'patient_uuid' => 'patient-uuid-123',
        'user_id' => $user->id,
        'source' => 'manual',
        'metadata' => ['foo' => 'bar'],
        'enrolled_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('patient.enrollment.show'));

    $response
        ->assertOk()
        ->assertJson([
            'enrollment' => [
                'patient_uuid' => 'patient-uuid-123',
                'user_id' => $user->id,
                'source' => 'manual',
            ],
        ])
        ->assertJsonPath('enrollment.metadata.foo', 'bar')
        ->assertJsonStructure([
            'enrollment' => [
                'patient_uuid',
                'user_id',
                'source',
                'metadata',
                'enrolled_at',
            ],
        ]);
    });


it('rejects manual enrollment for guests', function () {
    $response = $this->postJson(route('patient.enrollment.store'));

    $response->assertStatus(401);
});

it('creates a patient enrollment via the manual endpoint for non-enrolled users', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('patient.enrollment.store'));

    $response
        ->assertCreated()
        ->assertJson([
            'enrollment' => [
                'user_id' => $user->id,
                'source' => 'manual',
            ],
        ])
        ->assertJsonStructure([
            'enrollment' => [
                'patient_uuid',
                'user_id',
                'source',
                'metadata',
                'enrolled_at',
            ],
        ]);

    $this->assertDatabaseHas('patient_enrollments', [
        'user_id' => $user->id,
        'source' => 'manual',
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.enrolled')
        ->where('event_data->user_id', $user->id)
        ->where('metadata->source', 'manual')
        ->exists();

    expect($hasEvent)->toBeTrue();
});

it('is idempotent when the user is already enrolled', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $firstResponse = $this->postJson(route('patient.enrollment.store'));

    $firstResponse->assertCreated();

    $enrollment = $firstResponse->json('enrollment');

    $secondResponse = $this->postJson(route('patient.enrollment.store'));

    $secondResponse
        ->assertOk()
        ->assertJson([
            'enrollment' => [
                'patient_uuid' => $enrollment['patient_uuid'],
                'user_id' => $user->id,
                'source' => 'manual',
            ],
        ]);

    expect(PatientEnrollment::where('user_id', $user->id)->count())->toBe(1);

    $eventCount = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.enrolled')
        ->where('event_data->user_id', $user->id)
        ->count();

    expect($eventCount)->toBe(1);
});

