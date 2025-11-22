<?php

use App\Domain\Clinical\QuestionnaireAggregate;
use App\Domain\Clinical\Events\QuestionnaireCreated;
use App\Domain\Clinical\Events\ResponseSubmitted;

describe('QuestionnaireAggregate', function () {
    describe('create', function () {
        it('creates a new questionnaire aggregate with QuestionnaireCreated event', function () {
            $uuid = 'questionnaire-uuid-123';
            $payload = [
                'title' => 'Health Assessment',
                'description' => 'Initial health assessment',
                'questions' => [
                    ['id' => 1, 'text' => 'How are you feeling?', 'type' => 'text'],
                ],
                'created_by' => 1,
                'patient_id' => 'patient-123',
            ];

            $aggregate = QuestionnaireAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->title)->toBe('Health Assessment');
            expect($aggregate->status)->toBe('draft');

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(QuestionnaireCreated::class);
        });
    });

    describe('event application', function () {
        it('applies QuestionnaireCreated event correctly', function () {
            $payload = [
                'title' => 'Health Assessment',
                'description' => 'Initial health assessment',
                'questions' => [['id' => 1, 'text' => 'How are you feeling?']],
                'created_by' => 1,
                'patient_id' => 'patient-123',
            ];

            $aggregate = QuestionnaireAggregate::create('questionnaire-uuid-123', $payload);

            expect($aggregate->title)->toBe('Health Assessment');
            expect($aggregate->description)->toBe('Initial health assessment');
            expect($aggregate->createdBy)->toBe('1');
            expect($aggregate->patientId)->toBe('patient-123');
        });
    });
});

