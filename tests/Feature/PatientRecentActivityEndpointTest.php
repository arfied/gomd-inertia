<?php

use App\Models\Activity;
use App\Models\User;

it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.activity.recent'));

    $response->assertStatus(401);
});

it('returns an empty activities array when the user has no activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->getJson(route('patient.activity.recent'));

    $response
        ->assertOk()
        ->assertJson([
            'activities' => [],
        ]);
});

it('returns recent activities for the authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    Activity::create([
        'user_id' => $user->id,
        'logged_by' => $user->id,
        'type' => 'patient.enrolled',
        'description' => 'Patient enrolled in TeleMed Pro.',
        'metadata' => [
            'foo' => 'bar',
        ],
    ]);

    Activity::create([
        'user_id' => $user->id + 1,
        'logged_by' => $user->id,
        'type' => 'other.event',
        'description' => 'Some other activity.',
        'metadata' => null,
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('patient.activity.recent'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'activities' => [
                [
                    'id',
                    'type',
                    'description',
                    'metadata',
                    'created_at',
                ],
            ],
        ])
        ->assertJsonPath('activities.0.type', 'patient.enrolled')
        ->assertJsonPath('activities.0.description', 'Patient enrolled in TeleMed Pro.')
        ->assertJsonPath('activities.0.metadata.foo', 'bar');
});

