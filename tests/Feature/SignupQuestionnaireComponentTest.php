<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\QuestionnaireReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignupQuestionnaireComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_api_returns_questions_with_medication_filter(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'How are you feeling?', 'type' => 'text', 'required' => true],
            ['id' => 'q2', 'text' => 'Any allergies?', 'type' => 'checkbox', 'options' => ['Yes', 'No']],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-123',
            'title' => 'Signup Questionnaire',
            'description' => 'Initial questionnaire',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires?medication_name=Aspirin');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.0.text', 'How are you feeling?')
            ->assertJsonPath('data.0.type', 'text')
            ->assertJsonPath('data.0.required', true);
    }

    public function test_questionnaire_api_returns_questions_with_condition_filter(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Cardiovascular symptoms?', 'type' => 'textarea', 'section' => 'cardiovascular'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-456',
            'title' => 'Cardiovascular Questionnaire',
            'description' => 'Cardiovascular assessment',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires?condition_id=condition-123');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.section', 'cardiovascular');
    }

    public function test_questionnaire_api_supports_select_type(): void
    {
        $questions = [
            [
                'id' => 'q1',
                'text' => 'Select your option',
                'type' => 'select',
                'options' => [
                    ['value' => 'opt1', 'label' => 'Option 1'],
                    ['value' => 'opt2', 'label' => 'Option 2'],
                ],
            ],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-789',
            'title' => 'Select Questionnaire',
            'description' => 'Test select type',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.type', 'select')
            ->assertJsonCount(2, 'data.0.options');
    }

    public function test_questionnaire_api_supports_conditional_questions(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Do you have symptoms?', 'type' => 'radio', 'options' => ['Yes', 'No']],
            [
                'id' => 'q2',
                'text' => 'Describe your symptoms',
                'type' => 'textarea',
                'parent_question_id' => 'q1',
                'parent_answer_value' => 'Yes',
            ],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-cond',
            'title' => 'Conditional Questionnaire',
            'description' => 'Test conditional questions',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.parent_question_id', 'q1')
            ->assertJsonPath('data.1.parent_answer_value', 'Yes');
    }

    public function test_questionnaire_api_supports_date_and_number_types(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Date of birth', 'type' => 'date'],
            ['id' => 'q2', 'text' => 'Age', 'type' => 'number'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-types',
            'title' => 'Date and Number Questionnaire',
            'description' => 'Test date and number types',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.type', 'date')
            ->assertJsonPath('data.1.type', 'number');
    }

    public function test_questionnaire_api_returns_empty_when_no_active_questionnaire(): void
    {
        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}

