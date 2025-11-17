<?php

use App\Application\Order\PatientOrderFinder;
use App\Application\Order\Queries\GetPatientOrdersByPatientUuid;
use App\Application\Order\Queries\GetPatientOrdersByPatientUuidHandler;
use App\Models\MedicationOrder;
use Illuminate\Support\Collection;

it('returns orders for a patient UUID when they exist', function () {
    $order = new MedicationOrder();
    $order->id = 1;
    $order->patient_id = 42;

    $expected = collect([$order]);

    $finder = new class($expected) implements PatientOrderFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findByUserId(int $userId): Collection
        {
            return collect();
        }

        public function findByPatientUuid(string $patientUuid): Collection
        {
            return $this->result;
        }
    };

    $handler = new GetPatientOrdersByPatientUuidHandler($finder);

    $query = new GetPatientOrdersByPatientUuid(patientUuid: 'abc-123');

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns an empty collection when no orders exist for the given patient UUID', function () {
    $expected = collect();

    $finder = new class($expected) implements PatientOrderFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findByUserId(int $userId): Collection
        {
            return collect();
        }

        public function findByPatientUuid(string $patientUuid): Collection
        {
            return $this->result;
        }
    };

    $handler = new GetPatientOrdersByPatientUuidHandler($finder);

    $query = new GetPatientOrdersByPatientUuid(patientUuid: 'non-existent');

    $result = $handler->handle($query);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(0);
});

