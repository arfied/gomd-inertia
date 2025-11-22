<?php

use App\Domain\Clinical\ClinicalNoteAggregate;
use App\Domain\Clinical\Events\ClinicalNoteRecorded;

describe('ClinicalNoteAggregate', function () {
    describe('create', function () {
        it('creates a new clinical note aggregate with ClinicalNoteRecorded event', function () {
            $uuid = 'clinical-note-uuid-123';
            $payload = [
                'patient_id' => 'patient-123',
                'doctor_id' => 1,
                'note_type' => 'progress',
                'content' => 'Patient is improving',
                'attachments' => [],
                'recorded_at' => now()->toIso8601String(),
            ];

            $aggregate = ClinicalNoteAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->noteType)->toBe('progress');

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(ClinicalNoteRecorded::class);
        });
    });

    describe('event application', function () {
        it('applies ClinicalNoteRecorded event correctly', function () {
            $payload = [
                'patient_id' => 'patient-123',
                'doctor_id' => 1,
                'note_type' => 'assessment',
                'content' => 'Patient assessment complete',
                'attachments' => ['file1.pdf'],
                'recorded_at' => now()->toIso8601String(),
            ];

            $aggregate = ClinicalNoteAggregate::create('clinical-note-uuid-123', $payload);

            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->doctorId)->toBe('1');
            expect($aggregate->noteType)->toBe('assessment');
            expect($aggregate->content)->toBe('Patient assessment complete');
        });
    });
});

