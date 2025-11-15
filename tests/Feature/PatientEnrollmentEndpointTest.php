<?php

use App\Models\PatientEnrollment;
use App\Models\User;

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

