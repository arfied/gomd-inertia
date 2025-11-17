<?php

use App\Models\User;

it('redirects guests from staff patients page to login', function () {
    $response = $this->get(route('dashboard.patients'));

    $response->assertRedirect(route('login'));
});

it('forbids non-staff users from accessing staff patients page', function () {
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $response = $this->get(route('dashboard.patients'));

    $response->assertStatus(403);
});

it('allows staff users to view the staff patients page', function () {
    $staff = User::factory()->create();
    $staff->role = 'staff';

    $this->actingAs($staff);

    $response = $this->get(route('dashboard.patients'));

    $response->assertStatus(200);
});

