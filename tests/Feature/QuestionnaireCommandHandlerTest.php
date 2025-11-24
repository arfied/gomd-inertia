<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Application\Questionnaire\Commands\CreateQuestionnaire;
use App\Application\Questionnaire\Commands\SubmitQuestionnaireResponse;
use App\Application\Questionnaire\Handlers\CreateQuestionnaireHandler;
use App\Application\Questionnaire\Handlers\SubmitQuestionnaireResponseHandler;
use App\Services\EventStore;
use App\Models\StoredEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionnaireCommandHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_questionnaire_handler_stores_event(): void
    {
        $eventStore = $this->app->make(EventStore::class);
        $dispatcher = $this->app->make('events');
        $handler = new CreateQuestionnaireHandler($eventStore, $dispatcher);

        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        $command = new CreateQuestionnaire(
            $questionnaireId,
            'Test Questionnaire',
            'Description',
            $questions,
            'condition-1'
        );

        $handler->handle($command);

        $this->assertDatabaseHas('event_store', [
            'aggregate_uuid' => $questionnaireId,
            'aggregate_type' => 'questionnaire',
            'event_type' => 'questionnaire.created',
        ]);
    }

    public function test_submit_questionnaire_response_handler_stores_event(): void
    {
        $eventStore = $this->app->make(EventStore::class);
        $dispatcher = $this->app->make('events');

        // First create a questionnaire
        $createHandler = new CreateQuestionnaireHandler($eventStore, $dispatcher);
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        $createCommand = new CreateQuestionnaire(
            $questionnaireId,
            'Test Questionnaire',
            'Description',
            $questions
        );

        $createHandler->handle($createCommand);

        // Now submit a response
        $submitHandler = new SubmitQuestionnaireResponseHandler($eventStore, $dispatcher);
        $responses = ['q1' => 'Answer to question 1'];

        $submitCommand = new SubmitQuestionnaireResponse(
            $questionnaireId,
            'patient-1',
            $responses
        );

        $submitHandler->handle($submitCommand);

        $this->assertDatabaseHas('event_store', [
            'aggregate_uuid' => $questionnaireId,
            'aggregate_type' => 'questionnaire',
            'event_type' => 'questionnaire.response_submitted',
        ]);
    }

    public function test_create_questionnaire_handler_throws_on_invalid_command(): void
    {
        $eventStore = $this->app->make(EventStore::class);
        $dispatcher = $this->app->make('events');
        $handler = new CreateQuestionnaireHandler($eventStore, $dispatcher);

        $this->expectException(\TypeError::class);
        $handler->handle(new \stdClass());
    }

    public function test_submit_questionnaire_response_handler_throws_on_invalid_command(): void
    {
        $eventStore = $this->app->make(EventStore::class);
        $dispatcher = $this->app->make('events');
        $handler = new SubmitQuestionnaireResponseHandler($eventStore, $dispatcher);

        $this->expectException(\TypeError::class);
        $handler->handle(new \stdClass());
    }
}

