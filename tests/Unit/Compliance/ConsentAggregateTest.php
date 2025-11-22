<?php

use App\Domain\Compliance\ConsentAggregate;
use App\Domain\Compliance\Events\ConsentGranted;

describe('ConsentAggregate', function () {
    describe('create', function () {
        it('creates a new consent aggregate with ConsentGranted event', function () {
            $uuid = 'consent-uuid-123';
            $payload = [
                'patient_id' => 'patient-123',
                'consent_type' => 'treatment',
                'granted_by' => 1,
                'granted_at' => now()->toIso8601String(),
                'expires_at' => now()->addYear()->toIso8601String(),
                'terms_version' => '1.0',
                'status' => 'active',
            ];

            $aggregate = ConsentAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->consentType)->toBe('treatment');
            expect($aggregate->status)->toBe('active');

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(ConsentGranted::class);
        });
    });

    describe('event application', function () {
        it('applies ConsentGranted event correctly', function () {
            $payload = [
                'patient_id' => 'patient-123',
                'consent_type' => 'data_sharing',
                'granted_by' => 1,
                'granted_at' => now()->toIso8601String(),
                'expires_at' => now()->addYear()->toIso8601String(),
                'terms_version' => '2.0',
                'status' => 'active',
            ];

            $aggregate = ConsentAggregate::create('consent-uuid-123', $payload);

            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->consentType)->toBe('data_sharing');
            expect($aggregate->grantedBy)->toBe('1');
            expect($aggregate->termsVersion)->toBe('2.0');
        });
    });
});

