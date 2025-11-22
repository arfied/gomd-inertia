<?php

use App\Models\User;
use App\Models\ClinicalNoteReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/clinical/notes');

    $response->assertRedirect('/login');
});

it('renders clinical notes page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/clinical/notes');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('clinical/ClinicalNotes')
            ->has('notes')
        );
});

it('passes clinical notes data to the page', function () {
    $user = User::factory()->create();

    ClinicalNoteReadModel::create([
        'clinical_note_uuid' => 'note-uuid-123',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'content' => 'Patient presents with symptoms...',
        'note_type' => 'assessment',
        'recorded_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/clinical/notes');

    $response->assertInertia(fn ($page) => $page
        ->component('clinical/ClinicalNotes')
        ->has('notes.data', 1)
        ->where('notes.data.0.note_type', 'assessment')
    );
});

it('filters clinical notes by type', function () {
    $user = User::factory()->create();

    ClinicalNoteReadModel::create([
        'clinical_note_uuid' => 'note-uuid-123',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'content' => 'Assessment content',
        'note_type' => 'assessment',
        'recorded_at' => now(),
    ]);

    ClinicalNoteReadModel::create([
        'clinical_note_uuid' => 'note-uuid-456',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'content' => 'Progress content',
        'note_type' => 'progress',
        'recorded_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/clinical/notes?note_type=assessment');

    $response->assertInertia(fn ($page) => $page
        ->has('notes.data', 1)
        ->where('notes.data.0.note_type', 'assessment')
    );
});

it('paginates clinical notes', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        ClinicalNoteReadModel::create([
            'clinical_note_uuid' => "note-uuid-{$i}",
            'patient_id' => 'patient-123',
            'doctor_id' => $user->id,
            'content' => "Content {$i}",
            'note_type' => 'assessment',
            'recorded_at' => now(),
        ]);
    }

    $response = $this->actingAs($user)->get('/clinical/notes');

    $response->assertInertia(fn ($page) => $page
        ->has('notes.data', 15)
        ->where('notes.current_page', 1)
        ->where('notes.per_page', 15)
        ->where('notes.total', 20)
    );
});

it('shows specific clinical note', function () {
    $user = User::factory()->create();

    $note = ClinicalNoteReadModel::create([
        'clinical_note_uuid' => 'note-uuid-123',
        'patient_id' => 'patient-123',
        'doctor_id' => $user->id,
        'content' => 'Detailed content',
        'note_type' => 'assessment',
        'recorded_at' => now(),
    ]);

    $response = $this->actingAs($user)->get("/clinical/notes/{$note->clinical_note_uuid}");

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('clinical/ClinicalNotes')
            ->where('selectedNote.content', 'Detailed content')
        );
});

