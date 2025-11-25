<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Medication;
use App\Models\Condition;
use App\Models\SubscriptionPlan;
use App\Models\QuestionnaireReadModel;
use App\Models\SignupReadModel;
use App\Application\Commands\CommandBus;
use App\Application\Questionnaire\Commands\CreateQuestionnaire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignupFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_medication_first_signup_flow_with_questionnaire(): void
    {
        // Setup data
        $medication = Medication::factory()->create(['status' => 'approved', 'name' => 'Aspirin 500mg']);
        $plan = SubscriptionPlan::factory()->create(['name' => 'Basic Plan', 'price' => 99.99]);
        
        $questions = [
            ['id' => 'q1', 'text' => 'How are you?', 'type' => 'text', 'required' => true],
        ];

        // Create questionnaire
        $commandBus = app(CommandBus::class);
        $commandBus->dispatch(new CreateQuestionnaire(
            questionnaireId: 'questionnaire-uuid-flow',
            title: 'Test Questionnaire',
            description: 'Test',
            questions: $questions,
            conditionId: null,
        ));

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-flow',
            'title' => 'Test Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        // Step 1: Start signup
        $response = $this->postJson('/signup/start', ['signup_path' => 'medication_first']);
        $response->assertStatus(200);
        $signupId = $response->json('signup_id');
        expect($signupId)->not->toBeNull();

        // Step 2: Select medication
        $response = $this->postJson('/signup/select-medication', [
            'signup_id' => $signupId,
            'medication_name' => $medication->name,
        ]);
        $response->assertStatus(200);

        // Step 3: Select plan
        $response = $this->postJson('/signup/select-plan', [
            'signup_id' => $signupId,
            'plan_id' => $plan->id,
        ]);
        $response->assertStatus(200);

        // Step 4: Submit questionnaire
        $response = $this->postJson('/api/questionnaires/submit', [
            'questionnaire_uuid' => 'questionnaire-uuid-flow',
            'patient_id' => null,
            'responses' => ['q1' => 'I am feeling great'],
        ]);
        $response->assertStatus(200);

        // Verify questionnaire responses stored
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', 'questionnaire-uuid-flow')->first();
        expect($questionnaire->responses)->toEqual(['q1' => 'I am feeling great']);
        expect($questionnaire->status)->toBe('submitted');
    }

    public function test_questionnaire_api_returns_uuid_for_signup_flow(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Question 1', 'type' => 'text', 'required' => true],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-api',
            'title' => 'Signup Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/questionnaires');

        $response->assertStatus(200)
            ->assertJsonPath('questionnaire_uuid', 'questionnaire-uuid-api')
            ->assertJsonCount(1, 'data');
    }

    public function test_questionnaire_submission_with_medication_filter(): void
    {
        $questions = [
            ['id' => 'q1', 'text' => 'Medication question', 'type' => 'text', 'required' => true],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => 'questionnaire-uuid-med',
            'title' => 'Medication Questionnaire',
            'description' => 'Test',
            'questions' => $questions,
            'medication_name' => 'Aspirin 500mg',
            'status' => 'active',
        ]);

        // Create questionnaire via command
        $commandBus = app(CommandBus::class);
        $commandBus->dispatch(new CreateQuestionnaire(
            questionnaireId: 'questionnaire-uuid-med',
            title: 'Medication Questionnaire',
            description: 'Test',
            questions: $questions,
            conditionId: null,
        ));

        // Submit with medication filter
        $response = $this->getJson('/api/questionnaires?medication_name=Aspirin 500mg');

        $response->assertStatus(200)
            ->assertJsonPath('questionnaire_uuid', 'questionnaire-uuid-med');
    }
}

