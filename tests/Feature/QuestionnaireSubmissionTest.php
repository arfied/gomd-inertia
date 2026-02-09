<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\QuestionnaireReadModel;
use App\Application\Commands\CommandBus;
use App\Application\Questionnaire\Commands\CreateQuestionnaire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionnaireSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_questionnaire_with_valid_responses(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'How are you?', 'type' => 'text', 'required' => true],
            ['id' => 'q2', 'text' => 'Any symptoms?', 'type' => 'checkbox', 'options' => ['Yes', 'No']],
        ];

        // Create questionnaire via command bus
        $commandBus = app(CommandBus::class);
        $commandBus->dispatch(new CreateQuestionnaire(
            questionnaireId: 'questionnaire-uuid-123',
            title: 'Test Questionnaire',
            description: 'Test',
            questions: $questions,
            conditionId: null,
        ));

        // Ensure read model is created
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-123',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-123',
            'patient_id' => 'patient-123',
            'responses' => [
                'q1' => 'I am fine',
                'q2' => ['Yes'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Questionnaire submitted successfully');
    }

    public function test_submit_questionnaire_returns_404_for_missing_questionnaire(): void
    {
        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'non-existent-uuid',
            'patient_id' => 'patient-123',
            'responses' => ['q1' => 'answer'],
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Questionnaire not found');
    }

    public function test_submit_questionnaire_validates_required_fields(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Required field', 'type' => 'text', 'required' => true],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-456',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-456',
            'patient_id' => 'patient-123',
            'responses' => [
                'q1' => '',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonPath('errors.q1', 'This field is required');
    }

    public function test_submit_questionnaire_validates_number_type(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Age', 'type' => 'number', 'required' => true],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-789',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-789',
            'patient_id' => 'patient-123',
            'responses' => [
                'q1' => 'not-a-number',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.q1', 'Must be a valid number');
    }

    public function test_submit_questionnaire_validates_date_type(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Date of birth', 'type' => 'date', 'required' => true],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-date',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-date',
            'patient_id' => 'patient-123',
            'responses' => [
                'q1' => 'invalid-date',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.q1', 'Must be a valid date');
    }

    public function test_submit_questionnaire_validates_select_options(): void
    {
        $questions = [
            [
                'id' => 'q1',
                'text' => 'Select option',
                'type' => 'select',
                'required' => true,
                'options' => [
                    ['value' => 'opt1', 'label' => 'Option 1'],
                    ['value' => 'opt2', 'label' => 'Option 2'],
                ],
            ],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-select',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-select',
            'patient_id' => 'patient-123',
            'responses' => [
                'q1' => 'invalid-option',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.q1', 'Invalid option selected');
    }

    public function test_submit_questionnaire_allows_optional_patient_id(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Question', 'type' => 'text', 'required' => false],
        ];

        // Create questionnaire via command bus
        $commandBus = app(CommandBus::class);
        $commandBus->dispatch(new CreateQuestionnaire(
            questionnaireId: 'questionnaire-uuid-optional',
            title: 'Test Questionnaire',
            description: 'Test',
            questions: $questions,
            conditionId: null,
        ));

        // Ensure read model is created
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-optional',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-optional',
            'responses' => [
                'q1' => 'answer',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify response was stored with null patient_id
        $questionnaireResponse = \App\Models\QuestionnaireResponse::where('questionnaire_uuid', 'questionnaire-uuid-optional')
            ->first();
        expect($questionnaireResponse)->not->toBeNull();
        expect($questionnaireResponse->responses)->toEqual(['q1' => 'answer']);
        expect($questionnaireResponse->patient_id)->toBeNull();
    }
}

