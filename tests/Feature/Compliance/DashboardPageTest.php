<?php

use App\Models\User;
use App\Models\LicenseReadModel;
use App\Models\ConsentReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/compliance/dashboard');

    $response->assertRedirect('/login');
});

it('renders compliance dashboard page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/compliance/dashboard');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('compliance/Dashboard')
            ->has('licenses')
            ->has('consents')
        );
});

it('passes licenses data to the page', function () {
    $user = User::factory()->create();

    LicenseReadModel::create([
        'license_uuid' => 'license-uuid-123',
        'provider_id' => $user->id,
        'license_number' => 'LIC-123456',
        'license_type' => 'MD',
        'verified_at' => now()->subYear(),
        'expires_at' => now()->addYear(),
        'status' => 'verified',
    ]);

    $response = $this->actingAs($user)->get('/compliance/dashboard');

    $response->assertInertia(fn ($page) => $page
        ->component('compliance/Dashboard')
        ->has('licenses.data', 1)
        ->where('licenses.data.0.status', 'verified')
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

    $response = $this->actingAs($user)->get('/compliance/dashboard');

    $response->assertInertia(fn ($page) => $page
        ->component('compliance/Dashboard')
        ->has('consents.data', 1)
        ->where('consents.data.0.status', 'active')
    );
});

it('shows license summary statistics', function () {
    $user = User::factory()->create();

    LicenseReadModel::create([
        'license_uuid' => 'license-uuid-123',
        'provider_id' => $user->id,
        'license_number' => 'LIC-123456',
        'license_type' => 'MD',
        'verified_at' => now()->subYear(),
        'expires_at' => now()->addYear(),
        'status' => 'verified',
    ]);

    LicenseReadModel::create([
        'license_uuid' => 'license-uuid-456',
        'provider_id' => $user->id,
        'license_number' => 'LIC-456789',
        'license_type' => 'MD',
        'verified_at' => now()->subYears(2),
        'expires_at' => now()->subDay(),
        'status' => 'verified',
    ]);

    $response = $this->actingAs($user)->get('/compliance/dashboard');

    $response->assertInertia(fn ($page) => $page
        ->has('licenses.data', 2)
    );
});

it('paginates licenses', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        LicenseReadModel::create([
            'license_uuid' => "license-uuid-{$i}",
            'provider_id' => $user->id,
            'license_number' => "LIC-{$i}",
            'license_type' => 'MD',
            'verified_at' => now()->subYear(),
            'expires_at' => now()->addYear(),
            'status' => 'verified',
        ]);
    }

    $response = $this->actingAs($user)->get('/compliance/dashboard');

    $response->assertInertia(fn ($page) => $page
        ->has('licenses.data', 10)
        ->where('licenses.current_page', 1)
        ->where('licenses.per_page', 10)
        ->where('licenses.total', 20)
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

    $response = $this->actingAs($user)->get('/compliance/dashboard');

    $response->assertInertia(fn ($page) => $page
        ->has('consents.data', 10)
        ->where('consents.current_page', 1)
        ->where('consents.per_page', 10)
        ->where('consents.total', 20)
    );
});

