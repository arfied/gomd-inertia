<?php

use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Support\Str;

it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.events.timeline'));

    $response->assertStatus(401);
});

it('returns an empty events array when the user has no enrollment', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->getJson(route('patient.events.timeline'));

    $response
        ->assertOk()
        ->assertJson([
            'events' => [],
        ]);
});

it('returns a patient events timeline for the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $patientUuid = (string) Str::uuid();
    $otherPatientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $user->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    PatientEnrollment::create([
        'patient_uuid' => $otherPatientUuid,
        'user_id' => $otherUser->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    StoredEvent::create([
        'aggregate_uuid' => $patientUuid,
        'aggregate_type' => 'patient',
        'event_type' => 'patient.enrolled',
        'event_data' => [
            'user_id' => $user->id,
        ],
        'metadata' => [
            'foo' => 'bar',
        ],
        'occurred_at' => now()->subMinutes(5),
    ]);

    StoredEvent::create([
        'aggregate_uuid' => $otherPatientUuid,
        'aggregate_type' => 'patient',
        'event_type' => 'patient.enrolled',
        'event_data' => [
            'user_id' => $otherUser->id,
        ],
        'metadata' => null,
        'occurred_at' => now()->subMinutes(10),
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('patient.events.timeline'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'events' => [
                [
                    'id',
                    'aggregate_uuid',
                    'event_type',
                    'source',
                    'description',
                    'payload',
                    'metadata',
                    'occurred_at',
                ],
            ],
        ])
        ->assertJsonCount(1, 'events')
        ->assertJsonPath('events.0.aggregate_uuid', $patientUuid)
        ->assertJsonPath('events.0.event_type', 'patient.enrolled')
        ->assertJsonPath('events.0.description', 'Patient enrolled in TeleMed Pro.')
        ->assertJsonPath('events.0.source', null)
        ->assertJsonPath('events.0.payload.user_id', $user->id)
        ->assertJsonPath('events.0.metadata.foo', 'bar');
});

