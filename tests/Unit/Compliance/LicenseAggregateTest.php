<?php

use App\Domain\Compliance\LicenseAggregate;
use App\Domain\Compliance\Events\LicenseVerified;

describe('LicenseAggregate', function () {
    describe('create', function () {
        it('creates a new license aggregate with LicenseVerified event', function () {
            $uuid = 'license-uuid-123';
            $payload = [
                'provider_id' => 1,
                'license_number' => 'MD123456',
                'license_type' => 'md',
                'verified_at' => now()->toIso8601String(),
                'expires_at' => now()->addYears(3)->toIso8601String(),
                'issuing_body' => 'State Medical Board',
                'verification_url' => 'https://example.com/verify',
                'status' => 'verified',
            ];

            $aggregate = LicenseAggregate::create($uuid, $payload);

            expect($aggregate->uuid)->toBe($uuid);
            expect($aggregate->providerId)->toBe('1');
            expect($aggregate->licenseType)->toBe('md');
            expect($aggregate->status)->toBe('verified');

            $events = $aggregate->releaseEvents();
            expect($events)->toHaveCount(1);
            expect($events[0])->toBeInstanceOf(LicenseVerified::class);
        });
    });

    describe('event application', function () {
        it('applies LicenseVerified event correctly', function () {
            $payload = [
                'provider_id' => 1,
                'license_number' => 'RN789012',
                'license_type' => 'rn',
                'verified_at' => now()->toIso8601String(),
                'expires_at' => now()->addYears(2)->toIso8601String(),
                'issuing_body' => 'State Nursing Board',
                'verification_url' => 'https://example.com/verify',
                'status' => 'verified',
            ];

            $aggregate = LicenseAggregate::create('license-uuid-123', $payload);

            expect($aggregate->providerId)->toBe('1');
            expect($aggregate->licenseNumber)->toBe('RN789012');
            expect($aggregate->licenseType)->toBe('rn');
            expect($aggregate->issuingBody)->toBe('State Nursing Board');
        });
    });
});

