<?php

use App\Application\Order\PatientOrderFinder;
use App\Application\Order\Queries\GetPatientOrdersByUserId;
use App\Application\Order\Queries\GetPatientOrdersByUserIdHandler;
use App\Models\MedicationOrder;
use Illuminate\Support\Collection;

it('returns orders for a user when they exist', function () {
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
            return $this->result;
        }

        public function findByPatientUuid(string $patientUuid): Collection
        {
            return collect();
        }
    };

    $handler = new GetPatientOrdersByUserIdHandler($finder);

    $query = new GetPatientOrdersByUserId(userId: 42);

    $result = $handler->handle($query);

    expect($result)->toBe($expected);
});

it('returns an empty collection when no orders exist for the given user', function () {
    $expected = collect();

    $finder = new class($expected) implements PatientOrderFinder {
        public function __construct(private Collection $result)
        {
        }

        public function findByUserId(int $userId): Collection
        {
            return $this->result;
        }

        public function findByPatientUuid(string $patientUuid): Collection
        {
            return collect();
        }
    };

    $handler = new GetPatientOrdersByUserIdHandler($finder);

    $query = new GetPatientOrdersByUserId(userId: 999);

    $result = $handler->handle($query);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(0);
});

