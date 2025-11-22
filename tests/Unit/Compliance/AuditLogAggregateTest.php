<?php

use App\Domain\Compliance\AuditLogAggregate;
use App\Domain\Compliance\Events\AccessLogged;

describe('AuditLogAggregate', function () {
    describe('create', function () {
        it('creates a new audit log aggregate with AccessLogged event', function () {
            $uuid = 'audit-log-uuid-123';
            $payload = [
                'patient_id' => 'patient-123',
                'accessed_by' => 1,
                'access_type' => 'view',
                'resource' => 'medical_records',
                'accessed_at' => now()->toIso8601String(),
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
            ];

            $aggregate = AuditLogAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->accessType)->toBe('view');

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(AccessLogged::class);
        });
    });

    describe('event application', function () {
        it('applies AccessLogged event correctly', function () {
            $payload = [
                'patient_id' => 'patient-123',
                'accessed_by' => 1,
                'access_type' => 'export',
                'resource' => 'clinical_notes',
                'accessed_at' => now()->toIso8601String(),
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
            ];

            $aggregate = AuditLogAggregate::create('audit-log-uuid-123', $payload);

            expect($aggregate->patientId)->toBe('patient-123');
            expect($aggregate->accessedBy)->toBe('1');
            expect($aggregate->accessType)->toBe('export');
            expect($aggregate->resource)->toBe('clinical_notes');
        });
    });
});

