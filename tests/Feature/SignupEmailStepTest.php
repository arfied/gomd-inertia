<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create patient role if it doesn't exist
    if (!Role::where('name', 'patient')->exists()) {
        Role::create(['name' => 'patient', 'guard_name' => 'web']);
    }
});

it('creates patient user with email during signup', function () {
    // Start signup
    $startResponse = $this->postJson('/signup/start', [
        'signup_path' => 'medication_first',
    ]);

    expect($startResponse->status())->toBe(200);
    $signupId = $startResponse->json('signup_id');

    // Create patient user with email
    $email = 'patient@example.com';
    $response = $this->postJson('/signup/create-patient-user', [
        'signup_id' => $signupId,
        'email' => $email,
    ]);

    expect($response->status())->toBe(200);
    expect($response->json('success'))->toBeTrue();

    // Verify user was created
    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('patient'))->toBeTrue();
});

it('prevents duplicate email during signup', function () {
    // Create existing user
    $user = User::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password' => bcrypt('password'),
    ]);
    $user->assignRole('patient');

    // Start signup
    $startResponse = $this->postJson('/signup/start', [
        'signup_path' => 'medication_first',
    ]);

    $signupId = $startResponse->json('signup_id');

    // Try to create patient user with existing email
    $response = $this->postJson('/signup/create-patient-user', [
        'signup_id' => $signupId,
        'email' => 'existing@example.com',
    ]);

    expect($response->status())->toBe(422);
});

it('validates email format during signup', function () {
    // Start signup
    $startResponse = $this->postJson('/signup/start', [
        'signup_path' => 'medication_first',
    ]);

    $signupId = $startResponse->json('signup_id');

    // Try to create patient user with invalid email
    $response = $this->postJson('/signup/create-patient-user', [
        'signup_id' => $signupId,
        'email' => 'invalid-email',
    ]);

    expect($response->status())->toBe(422);
});

it('requires signup_id and email', function () {
    // Try without signup_id
    $response = $this->postJson('/signup/create-patient-user', [
        'email' => 'test@example.com',
    ]);

    expect($response->status())->toBe(422);

    // Try without email
    $response = $this->postJson('/signup/create-patient-user', [
        'signup_id' => 'some-uuid',
    ]);

    expect($response->status())->toBe(422);
});

