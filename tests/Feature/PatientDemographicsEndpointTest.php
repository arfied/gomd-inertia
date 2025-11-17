<?php

use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;

it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.demographics.show'));

    $response->assertStatus(401);

    $response = $this->putJson(route('patient.demographics.update'));

    $response->assertStatus(401);
});

it('returns demographics for the authenticated user based on existing user fields', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'fname' => 'Jane',
        'lname' => 'Doe',
        'gender' => 'F',
        'dob' => '1990-01-02',
        'address1' => '123 Main St',
        'address2' => 'Unit 4',
        'city' => 'Metropolis',
        'state' => 'NY',
        'zip' => '12345',
        'phone' => '555-0000',
        'mobile_phone' => '555-1111',
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('patient.demographics.show'));

    $response
        ->assertOk()
        ->assertJson([
            'demographics' => [
                'user_id' => $user->id,
                'fname' => 'Jane',
                'lname' => 'Doe',
                'gender' => 'F',
                'address1' => '123 Main St',
                'address2' => 'Unit 4',
                'city' => 'Metropolis',
                'state' => 'NY',
                'zip' => '12345',
                'phone' => '555-0000',
                'mobile_phone' => '555-1111',
            ],
        ])
        ->assertJsonPath('demographics.dob', '1990-01-02');
});

it('rejects demographics update when the patient is not enrolled', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->putJson(route('patient.demographics.update'), [
        'fname' => 'Jane',
    ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'Patient is not enrolled.',
        ]);
});

it('updates demographics via event-sourced command and projection', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'fname' => 'Old',
        'lname' => 'Name',
        'gender' => 'U',
    ]);

    $this->actingAs($user);

    // Enroll the patient via the existing endpoint so we have a patient_uuid
    $this->postJson(route('patient.enrollment.store'))
        ->assertCreated();

    $payload = [
        'fname' => 'New',
        'lname' => 'Patient',
        'gender' => 'F',
        'dob' => '1990-01-02',
        'address1' => '123 Main St',
        'city' => 'Metropolis',
        'state' => 'NY',
        'zip' => '12345',
        'phone' => '555-0000',
        'mobile_phone' => '555-1111',
    ];

    $response = $this->putJson(route('patient.demographics.update'), $payload);

    $response
        ->assertOk()
        ->assertJsonPath('demographics.fname', 'New')
        ->assertJsonPath('demographics.lname', 'Patient')
        ->assertJsonPath('demographics.gender', 'F')
        ->assertJsonPath('demographics.dob', '1990-01-02');

    $user->refresh();

    expect($user->fname)->toBe('New');
    expect($user->lname)->toBe('Patient');
    expect($user->gender)->toBe('F');
    expect($user->dob?->toDateString())->toBe('1990-01-02');

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.demographics_updated')
        ->where('event_data->user_id', $user->id)
        ->where('event_data->fname', 'New')
        ->exists();

    expect($hasEvent)->toBeTrue();
});

