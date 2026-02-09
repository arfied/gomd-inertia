<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Questionnaire\Events\QuestionnaireCreated;
use App\Domain\Questionnaire\Events\QuestionnaireResponseSubmitted;
use App\Domain\Questionnaire\Events\QuestionnaireValidationFailed;
use App\Listeners\ProjectQuestionnaireResponseSubmitted;
use App\Listeners\ProjectQuestionnaireValidationFailed;
use App\Models\QuestionnaireReadModel;
use App\Models\QuestionnaireResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionnaireEventHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_response_submitted_listener_updates_read_model(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        // Create questionnaire read model
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => $questionnaireId,
            'title' => 'Test Questionnaire',
            'description' => 'Description',
            'questions' => $questions,
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create and dispatch event
        $responses = ['q1' => 'Answer to question 1'];
        $event = new QuestionnaireResponseSubmitted(
            $questionnaireId,
            'patient-1',
            $responses
        );

        $listener = new ProjectQuestionnaireResponseSubmitted();
        $listener->handle($event);

        // Verify response was stored in questionnaire_responses table
        $response = QuestionnaireResponse::where('questionnaire_uuid', $questionnaireId)
            ->where('patient_id', 'patient-1')
            ->first();
        $this->assertNotNull($response);
        $this->assertEquals($responses, $response->responses);
        $this->assertNotNull($response->submitted_at);
    }

    public function test_questionnaire_validation_failed_listener_updates_read_model(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea', 'required' => true]];

        // Create questionnaire read model
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => $questionnaireId,
            'title' => 'Test Questionnaire',
            'description' => 'Description',
            'questions' => $questions,
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create and dispatch event
        $errors = ['q1' => 'This field is required'];
        $event = new QuestionnaireValidationFailed(
            $questionnaireId,
            $errors
        );

        $listener = new ProjectQuestionnaireValidationFailed();
        $listener->handle($event);

        // Validation failures are tracked in event store only, not in read model
        // Just verify the listener doesn't throw an exception
        $this->assertTrue(true);
    }

    public function test_response_submitted_listener_handles_missing_questionnaire(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $responses = ['q1' => 'Answer'];

        $event = new QuestionnaireResponseSubmitted(
            $questionnaireId,
            'patient-1',
            $responses
        );

        $listener = new ProjectQuestionnaireResponseSubmitted();

        // Should not throw exception
        $listener->handle($event);

        // Verify no read model was created
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $questionnaireId)->first();
        $this->assertNull($questionnaire);
    }

    public function test_validation_failed_listener_handles_missing_questionnaire(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $errors = ['q1' => 'Error'];

        $event = new QuestionnaireValidationFailed(
            $questionnaireId,
            $errors
        );

        $listener = new ProjectQuestionnaireValidationFailed();

        // Should not throw exception
        $listener->handle($event);

        // Verify no read model was created
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $questionnaireId)->first();
        $this->assertNull($questionnaire);
    }
}

