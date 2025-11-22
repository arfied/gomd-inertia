<?php

use App\Models\User;
use App\Models\ConsentReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/compliance/consents');

    $response->assertRedirect('/login');
});

it('renders consents page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/compliance/consents');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('compliance/Dashboard')
            ->has('consents')
        );
});

it('passes consents data to the page', function () {
    $user = User::factory()->create();

    ConsentReadModel::create([
        'consent_uuid' => 'consent-uuid-123',
        'patient_id' => 'patient-123',
        'consent_type' => 'treatment',
        'status' => 'active',
        'granted_at' => now(),
        'granted_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/compliance/consents');

    $response->assertInertia(fn ($page) => $page
        ->component('compliance/Dashboard')
        ->has('consents.data', 1)
        ->where('consents.data.0.consent_type', 'treatment')
    );
});

it('filters consents by type', function () {
    $user = User::factory()->create();

    ConsentReadModel::create([
        'consent_uuid' => 'consent-uuid-123',
        'patient_id' => 'patient-123',
        'consent_type' => 'treatment',
        'status' => 'active',
        'granted_at' => now(),
        'granted_by' => $user->id,
    ]);

    ConsentReadModel::create([
        'consent_uuid' => 'consent-uuid-456',
        'patient_id' => 'patient-123',
        'consent_type' => 'research',
        'status' => 'active',
        'granted_at' => now(),
        'granted_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/compliance/consents?consent_type=treatment');

    $response->assertInertia(fn ($page) => $page
        ->has('consents.data', 1)
        ->where('consents.data.0.consent_type', 'treatment')
    );
});

it('filters consents by status', function () {
    $user = User::factory()->create();

    ConsentReadModel::create([
        'consent_uuid' => 'consent-uuid-123',
        'patient_id' => 'patient-123',
        'consent_type' => 'treatment',
        'status' => 'active',
        'granted_at' => now(),
        'granted_by' => $user->id,
    ]);

    ConsentReadModel::create([
        'consent_uuid' => 'consent-uuid-456',
        'patient_id' => 'patient-123',
        'consent_type' => 'treatment',
        'status' => 'revoked',
        'granted_at' => now()->subDay(),
        'granted_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/compliance/consents?status=active');

    $response->assertInertia(fn ($page) => $page
        ->has('consents.data', 1)
        ->where('consents.data.0.status', 'active')
    );
});

it('paginates consents', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        ConsentReadModel::create([
            'consent_uuid' => "consent-uuid-{$i}",
            'patient_id' => "patient-{$i}",
            'consent_type' => 'treatment',
            'status' => 'active',
            'granted_at' => now(),
            'granted_by' => $user->id,
        ]);
    }

    $response = $this->actingAs($user)->get('/compliance/consents');

    $response->assertInertia(fn ($page) => $page
        ->has('consents.data', 15)
        ->where('consents.current_page', 1)
        ->where('consents.per_page', 15)
        ->where('consents.total', 20)
    );
});



