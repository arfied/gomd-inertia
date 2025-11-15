<?php

use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuidHandler;
use App\Models\PatientEnrollment;

it('returns a patient enrollment for a patient UUID when one exists', function () {
    $expected = new PatientEnrollment();
    $expected->patient_uuid = 'patient-uuid-123';
    $expected->user_id = 42;

    $finder = new class($expected) implements PatientEnrollmentFinder {
        public function __construct(private ?PatientEnrollment $result)
        {
        }

        public function findByUserId(int $userId): ?PatientEnrollment
        {
            return null; // not used in this test
        }

        public function findByPatientUuid(string $patientUuid): ?PatientEnrollment
        {
            return $this->result;
        }
    };

    $handler = new GetPatientEnrollmentByPatientUuidHandler($finder);

    $query = new GetPatientEnrollmentByPatientUuid(patientUuid: 'patient-uuid-123');

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns null when no enrollment exists for the given patient UUID', function () {
    $finder = new class(null) implements PatientEnrollmentFinder {
        public function __construct(private ?PatientEnrollment $result)
        {
        }

        public function findByUserId(int $userId): ?PatientEnrollment
        {
            return null; // not used in this test
        }

        public function findByPatientUuid(string $patientUuid): ?PatientEnrollment
        {
            return $this->result;
        }
    };

    $handler = new GetPatientEnrollmentByPatientUuidHandler($finder);

    $query = new GetPatientEnrollmentByPatientUuid(patientUuid: 'non-existent-uuid');

    $result = $handler->handle($query);

    expect($result)->toBeNull();
});

