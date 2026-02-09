<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\QuestionnaireReadModel;
use App\Application\Commands\CommandBus;
use App\Application\Questionnaire\Commands\CreateQuestionnaire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionnaireSubmissionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_api_returns_uuid(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'How are you?', 'type' => 'text', 'required' => true],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-123',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonPath('questionnaire_uuid', 'questionnaire-uuid-123')
            ->assertJsonCount(1, 'data');
    }

    public function test_questionnaire_submission_stores_responses(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'How are you?', 'type' => 'text', 'required' => true],
        ];

        // Create questionnaire via command bus
        $commandBus = app(CommandBus::class);
        $commandBus->dispatch(new CreateQuestionnaire(
            questionnaireId: 'questionnaire-uuid-456',
            title: 'Test Questionnaire',
            description: 'Test',
            questions: $questions,
            conditionId: null,
        ));

        // Create read model
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-456',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Submit responses
        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-456',
            'patient_id' => 'patient-123',
            'responses' => [
                'q1' => 'I am feeling great',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify responses are stored in questionnaire_responses table
        $questionnaireResponse = \App\Models\QuestionnaireResponse::where('questionnaire_uuid', 'questionnaire-uuid-456')
            ->where('patient_id', 'patient-123')
            ->first();
        expect($questionnaireResponse)->not->toBeNull();
        expect($questionnaireResponse->responses)->toEqual(['q1' => 'I am feeling great']);
    }

    public function test_questionnaire_submission_without_uuid_fails(): void
    {
        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => '',
            'responses' => ['q1' => 'answer'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['questionnaire_uuid']);
    }

    public function test_questionnaire_submission_with_validation_errors(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Age', 'type' => 'number', 'required' => true],
        ];

        // Create questionnaire
        $commandBus = app(CommandBus::class);
        $commandBus->dispatch(new CreateQuestionnaire(
            questionnaireId: 'questionnaire-uuid-789',
            title: 'Test Questionnaire',
            description: 'Test',
            questions: $questions,
            conditionId: null,
        ));

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-789',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Submit with invalid number
        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-789',
            'responses' => [
                'q1' => 'not-a-number',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.q1', 'Must be a valid number');
    }
}

