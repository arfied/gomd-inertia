<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\QuestionnaireReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignupQuestionnaireComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_api_returns_all_questions(): void
    {
        $questions = [
            // General health question
            ['id' => 'q1', 'text' => 'Do you have any allergies?', 'type' => 'text', 'required' => true],
            // Another question
            ['id' => 'q2', 'text' => 'Any heart-related symptoms?', 'type' => 'checkbox', 'options' => ['Yes', 'No']],
            // Third question
            ['id' => 'q3', 'text' => 'Neurological symptoms?', 'type' => 'textarea'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-123',
            'title' => 'Signup Questionnaire',
            'description' => 'Initial questionnaire',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires?medication_names=Lisinopril');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.id', 'q2')
            ->assertJsonPath('data.2.id', 'q3');
    }

    public function test_questionnaire_api_ignores_condition_filter(): void
    {
        $questions = [
            // General health question
            ['id' => 'q1', 'text' => 'Any allergies?', 'type' => 'textarea', 'section' => 'general'],
            // Another question
            ['id' => 'q2', 'text' => 'Cardiovascular symptoms?', 'type' => 'textarea', 'section' => 'cardiovascular'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-456',
            'title' => 'Cardiovascular Questionnaire',
            'description' => 'Cardiovascular assessment',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request with condition_id should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires?condition_id=condition-123');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.section', 'cardiovascular');
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
                'medication_names' => [],
                'condition_id' => null,
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
            ['id' => 'q1', 'text' => 'Do you have symptoms?', 'type' => 'radio', 'options' => ['Yes', 'No'], 'medication_names' => [], 'condition_id' => null],
            [
                'id' => 'q2',
                'text' => 'Describe your symptoms',
                'type' => 'textarea',
                'parent_question_id' => 'q1',
                'parent_answer_value' => 'Yes',
                'medication_names' => [],
                'condition_id' => null,
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
            ['id' => 'q1', 'text' => 'Date of birth', 'type' => 'date', 'medication_names' => [], 'condition_id' => null],
            ['id' => 'q2', 'text' => 'Age', 'type' => 'number', 'medication_names' => [], 'condition_id' => null],
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

    public function test_questionnaire_api_returns_all_questions_regardless_of_medications(): void
    {
        $questions = [
            // General health question
            ['id' => 'q1', 'text' => 'Any allergies?', 'type' => 'text'],
            // Another question
            ['id' => 'q2', 'text' => 'Heart symptoms?', 'type' => 'textarea'],
            // Third question
            ['id' => 'q3', 'text' => 'Neurological symptoms?', 'type' => 'textarea'],
            // Fourth question
            ['id' => 'q4', 'text' => 'Any side effects?', 'type' => 'checkbox', 'options' => ['Yes', 'No']],
            // Fifth question
            ['id' => 'q5', 'text' => 'Mental health?', 'type' => 'radio', 'options' => ['Yes', 'No']],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-multi',
            'title' => 'Multi-Medication Questionnaire',
            'description' => 'Test multiple medication filtering',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request with both Lisinopril and Gabapentin should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires?medication_names=Lisinopril,Gabapentin');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.id', 'q2')
            ->assertJsonPath('data.2.id', 'q3')
            ->assertJsonPath('data.3.id', 'q4')
            ->assertJsonPath('data.4.id', 'q5');
    }

    public function test_questionnaire_api_returns_all_questions_with_single_medication(): void
    {
        $questions = [
            // General health question
            ['id' => 'q1', 'text' => 'Any allergies?', 'type' => 'text'],
            // Another question
            ['id' => 'q2', 'text' => 'Heart symptoms?', 'type' => 'textarea'],
            // Third question
            ['id' => 'q3', 'text' => 'Neurological symptoms?', 'type' => 'textarea'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-single',
            'title' => 'Single-Medication Questionnaire',
            'description' => 'Test single medication filtering',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request with only Lisinopril should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires?medication_names=Lisinopril');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.id', 'q2')
            ->assertJsonPath('data.2.id', 'q3');
    }

    public function test_questionnaire_api_returns_all_questions_without_medications(): void
    {
        $questions = [
            // General health questions
            ['id' => 'q1', 'text' => 'Date of birth?', 'type' => 'date'],
            ['id' => 'q2', 'text' => 'Any allergies?', 'type' => 'text'],
            // Other questions
            ['id' => 'q3', 'text' => 'Heart symptoms?', 'type' => 'textarea'],
            ['id' => 'q4', 'text' => 'Neurological symptoms?', 'type' => 'textarea'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-no-meds',
            'title' => 'No-Medication Questionnaire',
            'description' => 'Test without medications',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request without medications should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.id', 'q2')
            ->assertJsonPath('data.2.id', 'q3')
            ->assertJsonPath('data.3.id', 'q4');
    }

    public function test_questionnaire_api_returns_all_pain_medication_questions(): void
    {
        $questions = [
            // General health questions
            ['id' => 'q1', 'text' => 'Date of birth?', 'type' => 'date'],
            ['id' => 'q2', 'text' => 'Any allergies?', 'type' => 'text'],
            // Pain medication questions
            ['id' => 'q10', 'text' => 'What type of pain?', 'type' => 'checkbox', 'options' => ['Headache', 'Muscle pain']],
            ['id' => 'q11', 'text' => 'How often pain?', 'type' => 'select', 'options' => ['Daily', 'Weekly']],
            ['id' => 'q12', 'text' => 'Stomach issues?', 'type' => 'radio', 'options' => ['Yes', 'No']],
            ['id' => 'q13', 'text' => 'Liver disease?', 'type' => 'radio', 'options' => ['Yes', 'No']],
            ['id' => 'q14', 'text' => 'Kidney disease?', 'type' => 'radio', 'options' => ['Yes', 'No']],
            // Other medication questions
            ['id' => 'q4', 'text' => 'Heart symptoms?', 'type' => 'textarea'],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-pain',
            'title' => 'Pain-Medication Questionnaire',
            'description' => 'Test pain medication filtering',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request with Acetaminophen should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires?medication_names=Acetaminophen');

        $response->assertStatus(200)
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.id', 'q2')
            ->assertJsonPath('data.2.id', 'q10')
            ->assertJsonPath('data.3.id', 'q11')
            ->assertJsonPath('data.4.id', 'q12')
            ->assertJsonPath('data.5.id', 'q13')
            ->assertJsonPath('data.6.id', 'q14')
            ->assertJsonPath('data.7.id', 'q4');
    }

    public function test_questionnaire_api_returns_all_ibuprofen_questions(): void
    {
        $questions = [
            // General health questions
            ['id' => 'q1', 'text' => 'Date of birth?', 'type' => 'date'],
            // Pain medication questions
            ['id' => 'q10', 'text' => 'What type of pain?', 'type' => 'checkbox', 'options' => ['Headache']],
            ['id' => 'q13', 'text' => 'Liver disease?', 'type' => 'radio', 'options' => ['Yes', 'No']],
            ['id' => 'q14', 'text' => 'Kidney disease?', 'type' => 'radio', 'options' => ['Yes', 'No']],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-ibuprofen',
            'title' => 'Ibuprofen-Medication Questionnaire',
            'description' => 'Test ibuprofen filtering',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Request with Ibuprofen should return all questions (no filtering)
        $response = $this->getJson('/api/questionnaires?medication_names=Ibuprofen');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.id', 'q1')
            ->assertJsonPath('data.1.id', 'q10')
            ->assertJsonPath('data.2.id', 'q13')
            ->assertJsonPath('data.3.id', 'q14');
    }
}

