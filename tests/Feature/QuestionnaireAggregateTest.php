<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Questionnaire\QuestionnaireAggregate;
use App\Domain\Questionnaire\Events\QuestionnaireCreated;
use App\Domain\Questionnaire\Events\QuestionnaireResponseSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionnaireAggregateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_questionnaire_aggregate(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [
            ['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea', 'required' => true],
            ['id' => 'q2', 'text' => 'Question 2', 'type' => 'select', 'required' => false],
        ];

        $aggregate = QuestionnaireAggregate::create(
            $questionnaireId,
            'Test Questionnaire',
            'A test questionnaire',
            $questions,
            'condition-1'
        );

        $this->assertEquals($questionnaireId, $aggregate->questionnaireId);
        $this->assertEquals('Test Questionnaire', $aggregate->title);
        $this->assertEquals('A test questionnaire', $aggregate->description);
        $this->assertEquals($questions, $aggregate->questions);
        $this->assertEquals('condition-1', $aggregate->conditionId);
    }

    public function test_aggregate_records_questionnaire_created_event(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        $aggregate = QuestionnaireAggregate::create(
            $questionnaireId,
            'Test Questionnaire',
            'Description',
            $questions
        );

        $events = $aggregate->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(QuestionnaireCreated::class, $events[0]);
        $this->assertEquals($questionnaireId, $events[0]->questionnaireId);
    }

    public function test_can_submit_questionnaire_response(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        $aggregate = QuestionnaireAggregate::create(
            $questionnaireId,
            'Test Questionnaire',
            'Description',
            $questions
        );

        $aggregate->releaseEvents(); // Clear created event

        $responses = ['q1' => 'Answer to question 1'];
        $aggregate->submitResponse('patient-1', $responses);

        $events = $aggregate->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(QuestionnaireResponseSubmitted::class, $events[0]);
        $this->assertEquals('patient-1', $events[0]->patientId);
        $this->assertEquals($responses, $events[0]->responses);
    }

    public function test_aggregate_applies_questionnaire_created_event(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        $event = new QuestionnaireCreated(
            $questionnaireId,
            'Test Questionnaire',
            'Description',
            $questions,
            'condition-1'
        );

        $aggregate = QuestionnaireAggregate::reconstituteFromHistory([$event]);

        $this->assertEquals($questionnaireId, $aggregate->questionnaireId);
        $this->assertEquals('Test Questionnaire', $aggregate->title);
        $this->assertEquals('Description', $aggregate->description);
        $this->assertEquals($questions, $aggregate->questions);
        $this->assertEquals('condition-1', $aggregate->conditionId);
    }

    public function test_aggregate_applies_response_submitted_event(): void
    {
        $questionnaireId = 'q-' . uniqid();
        $questions = [['id' => 'q1', 'text' => 'Question 1', 'type' => 'textarea']];

        $createdEvent = new QuestionnaireCreated(
            $questionnaireId,
            'Test Questionnaire',
            'Description',
            $questions
        );

        $responses = ['q1' => 'Answer'];
        $responseEvent = new QuestionnaireResponseSubmitted(
            $questionnaireId,
            'patient-1',
            $responses
        );

        $aggregate = QuestionnaireAggregate::reconstituteFromHistory([$createdEvent, $responseEvent]);

        $this->assertTrue($aggregate->isSubmitted);
        $this->assertEquals($responses, $aggregate->responses);
    }
}

