<?php

use App\Models\User;
use App\Models\QuestionnaireReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns unauthorized for guests', function () {
    $response = $this->getJson('/api/questionnaires');

    $response->assertStatus(401);
});

it('returns empty list when no questionnaires exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/questionnaires');

    $response->assertStatus(200)
        ->assertJsonPath('data', []);
});

it('returns questionnaires for authenticated user', function () {
    $user = User::factory()->create();

    QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'questionnaire-uuid-123',
        'title' => 'Health Assessment',
        'description' => 'Initial assessment',
        'questions' => json_encode([['id' => 1, 'text' => 'How are you?']]),
        'created_by' => $user->id,
        'patient_id' => 'patient-123',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->getJson('/api/questionnaires');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Health Assessment');
});

it('creates a questionnaire via API', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/questionnaires', [
        'title' => 'New Questionnaire',
        'description' => 'Test questionnaire',
        'questions' => [['id' => 1, 'text' => 'Question 1']],
        'patient_id' => 'patient-123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('message', 'Questionnaire created successfully')
        ->assertJsonStructure(['questionnaire_uuid', 'message']);
});

it('retrieves a specific questionnaire', function () {
    $user = User::factory()->create();

    $questionnaire = QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'questionnaire-uuid-456',
        'title' => 'Specific Questionnaire',
        'description' => 'Test',
        'questions' => json_encode([]),
        'created_by' => $user->id,
        'patient_id' => 'patient-123',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->getJson("/api/questionnaires/{$questionnaire->questionnaire_uuid}");

    $response->assertStatus(200)
        ->assertJsonPath('title', 'Specific Questionnaire');
});

