<?php

use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserIdHandler;
use App\Models\PatientEnrollment;

it('returns a patient enrollment for a user when one exists', function () {
    $expected = new PatientEnrollment();
    $expected->patient_uuid = 'patient-uuid-123';
    $expected->user_id = 42;

    $finder = new class($expected) implements PatientEnrollmentFinder {
        public function __construct(private ?PatientEnrollment $result)
        {
        }

        public function findByUserId(int $userId): ?PatientEnrollment
        {
            return $this->result;
        }
    };

    $handler = new GetPatientEnrollmentByUserIdHandler($finder);

    $query = new GetPatientEnrollmentByUserId(userId: 42);

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns null when no enrollment exists for the given user', function () {
    $finder = new class(null) implements PatientEnrollmentFinder {
        public function __construct(private ?PatientEnrollment $result)
        {
        }

        public function findByUserId(int $userId): ?PatientEnrollment
        {
            return $this->result;
        }
    };

    $handler = new GetPatientEnrollmentByUserIdHandler($finder);

    $query = new GetPatientEnrollmentByUserId(userId: 999);

    $result = $handler->handle($query);

    expect($result)->toBeNull();
});

