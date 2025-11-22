<?php

use App\Models\User;
use App\Models\ConsultationReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/clinical/consultations');

    $response->assertRedirect('/login');
});

it('renders consultations page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/clinical/consultations');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('clinical/Consultations')
            ->has('consultations')
        );
});

it('passes consultations data to the page', function () {
    $user = User::factory()->create();

    ConsultationReadModel::create([
        'consultation_uuid' => 'consultation-uuid-123',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'scheduled_at' => now()->addDay(),
        'reason' => 'Initial consultation',
        'status' => 'scheduled',
        'notes' => 'Initial consultation',
    ]);

    $response = $this->actingAs($user)->get('/clinical/consultations');

    $response->assertInertia(fn ($page) => $page
        ->component('clinical/Consultations')
        ->has('consultations.data', 1)
        ->where('consultations.data.0.status', 'scheduled')
    );
});

it('filters consultations by status', function () {
    $user = User::factory()->create();

    ConsultationReadModel::create([
        'consultation_uuid' => 'consultation-uuid-123',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'scheduled_at' => now()->addDay(),
        'duration' => 30,
        'status' => 'scheduled',
        'notes' => 'Scheduled consultation',
    ]);

    ConsultationReadModel::create([
        'consultation_uuid' => 'consultation-uuid-456',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'scheduled_at' => now()->subDay(),
        'duration' => 30,
        'status' => 'completed',
        'notes' => 'Completed consultation',
    ]);

    $response = $this->actingAs($user)->get('/clinical/consultations?status=scheduled');

    $response->assertInertia(fn ($page) => $page
        ->has('consultations.data', 1)
        ->where('consultations.data.0.status', 'scheduled')
    );
});

it('paginates consultations', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        ConsultationReadModel::create([
            'consultation_uuid' => "consultation-uuid-{$i}",
            'patient_id' => 'patient-123',
            'doctor_id' => $user->id,
            'scheduled_at' => now()->addDays($i),
            'reason' => "Consultation {$i}",
            'status' => 'scheduled',
            'notes' => "Consultation {$i}",
        ]);
    }

    $response = $this->actingAs($user)->get('/clinical/consultations');

    $response->assertInertia(fn ($page) => $page
        ->has('consultations.data', 15)
        ->where('consultations.current_page', 1)
        ->where('consultations.per_page', 15)
        ->where('consultations.total', 20)
    );
});

it('shows specific consultation', function () {
    $user = User::factory()->create();

    $consultation = ConsultationReadModel::create([
        'consultation_uuid' => 'consultation-uuid-123',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'scheduled_at' => now()->addDay(),
        'reason' => 'Specific consultation',
        'status' => 'scheduled',
        'notes' => 'Specific consultation',
    ]);

    $response = $this->actingAs($user)->get("/clinical/consultations/{$consultation->consultation_uuid}");

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('clinical/Consultations')
            ->where('selectedConsultation.notes', 'Specific consultation')
        );
});

