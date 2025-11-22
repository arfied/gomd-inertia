<?php

use App\Models\User;
use App\Models\QuestionnaireReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/clinical/questionnaires');

    $response->assertRedirect('/login');
});

it('renders questionnaires page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/clinical/questionnaires');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('clinical/Questionnaires')
            ->has('questionnaires')
        );
});

it('passes questionnaires data to the page', function () {
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

    $response = $this->actingAs($user)->get('/clinical/questionnaires');

    $response->assertInertia(fn ($page) => $page
        ->component('clinical/Questionnaires')
        ->has('questionnaires.data', 1)
        ->where('questionnaires.data.0.title', 'Health Assessment')
    );
});

it('filters questionnaires by status', function () {
    $user = User::factory()->create();

    QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'questionnaire-uuid-123',
        'title' => 'Active Questionnaire',
        'description' => 'Active',
        'questions' => json_encode([]),
        'created_by' => $user->id,
        'patient_id' => 'patient-123',
        'status' => 'active',
    ]);

    QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'questionnaire-uuid-456',
        'title' => 'Inactive Questionnaire',
        'description' => 'Inactive',
        'questions' => json_encode([]),
        'created_by' => $user->id,
        'patient_id' => 'patient-123',
        'status' => 'inactive',
    ]);

    $response = $this->actingAs($user)->get('/clinical/questionnaires?status=active');

    $response->assertInertia(fn ($page) => $page
        ->has('questionnaires.data', 1)
        ->where('questionnaires.data.0.status', 'active')
    );
});

it('paginates questionnaires', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => "questionnaire-uuid-{$i}",
            'title' => "Questionnaire {$i}",
            'description' => "Description {$i}",
            'questions' => json_encode([]),
            'created_by' => $user->id,
            'patient_id' => 'patient-123',
            'status' => 'active',
        ]);
    }

    $response = $this->actingAs($user)->get('/clinical/questionnaires');

    $response->assertInertia(fn ($page) => $page
        ->has('questionnaires.data', 15)
        ->where('questionnaires.current_page', 1)
        ->where('questionnaires.per_page', 15)
        ->where('questionnaires.total', 20)
    );
});

it('shows specific questionnaire', function () {
    $user = User::factory()->create();

    $questionnaire = QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'questionnaire-uuid-123',
        'title' => 'Specific Questionnaire',
        'description' => 'Test',
        'questions' => json_encode([['id' => 1, 'text' => 'Question 1']]),
        'created_by' => $user->id,
        'patient_id' => 'patient-123',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get("/clinical/questionnaires/{$questionnaire->questionnaire_uuid}");

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('clinical/Questionnaires')
            ->where('selectedQuestionnaire.title', 'Specific Questionnaire')
        );
});

