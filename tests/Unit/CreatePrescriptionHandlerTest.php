<?php

use App\Application\Prescription\Commands\CreatePrescription;
use App\Application\Prescription\Handlers\CreatePrescriptionHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\Prescription\Events\PrescriptionCreated;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a PrescriptionCreated event via the handler', function () {
    $fakeStore = new class implements EventStoreContract {
        /** @var array<int, DomainEvent> */
        public array $stored = [];

        public function store(DomainEvent $event): StoredEvent
        {
            $this->stored[] = $event;

            return new class extends StoredEvent {
                public function __construct()
                {
                    // Intentionally bypass Eloquent's constructor / database layer.
                }
            };
        }
    };

    $fakeDispatcher = new class implements Dispatcher {
        /** @var array<int, object> */
        public array $dispatched = [];

        public function listen($events, $listener = null): void
        {
            // No-op for this fake.
        }

        public function hasListeners($eventName): bool
        {
            return false;
        }

        public function subscribe($subscriber): void
        {
            // No-op for this fake.
        }

        public function until($event, $payload = [])
        {
            return null;
        }

        public function dispatch($event, $payload = [], $halt = false)
        {
            $this->dispatched[] = $event;

            return null;
        }

        public function push($event, $payload = []): void
        {
            // No-op for this fake.
        }

        public function flush($event): void
        {
            // No-op for this fake.
        }

        public function forget($event): void
        {
            // No-op for this fake.
        }

        public function forgetPushed(): void
        {
            // No-op for this fake.
        }
    };

    $handler = new CreatePrescriptionHandler($fakeStore, $fakeDispatcher);

    $command = new CreatePrescription(
        prescriptionUuid: 'rx-uuid-123',
        patientId: 42,
        doctorId: 7,
        notes: 'Take with food',
        isNonStandard: true,
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(PrescriptionCreated::class)
        ->and($event->aggregateUuid)->toBe('rx-uuid-123')
        ->and($event->payload)->toBe([
            'user_id' => 42,
            'doctor_id' => 7,
            'pharmacist_id' => null,
            'status' => 'pending',
            'notes' => 'Take with food',
            'is_non_standard' => true,
        ])
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

