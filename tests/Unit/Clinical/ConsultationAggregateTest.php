<?php

use App\Domain\Clinical\ConsultationAggregate;
use App\Domain\Clinical\Events\ConsultationScheduled;

describe('ConsultationAggregate', function () {
    describe('create', function () {
        it('creates a new consultation aggregate with ConsultationScheduled event', function () {
            $uuid = 'consultation-uuid-123';
            $payload = [
                'patient_id' => 'patient-123',
                'doctor_id' => 1,
                'scheduled_at' => now()->addDay()->toIso8601String(),
                'reason' => 'Follow-up visit',
                'notes' => 'Patient needs medication review',
                'status' => 'scheduled',
            ];

            $aggregate = ConsultationAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->status)->toBe('scheduled');

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(ConsultationScheduled::class);
        });
    });

    describe('event application', function () {
        it('applies ConsultationScheduled event correctly', function () {
            $scheduledAt = now()->addDay()->toIso8601String();
            $payload = [
                'patient_id' => 'patient-123',
                'doctor_id' => 1,
                'scheduled_at' => $scheduledAt,
                'notes' => 'First time patient',
                'status' => 'scheduled',
            ];

            $aggregate = ConsultationAggregate::create('consultation-uuid-123', $payload);

            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->doctorId)->toBe('1');
            expect($aggregate->notes)->toBe('First time patient');
        });
    });
});

