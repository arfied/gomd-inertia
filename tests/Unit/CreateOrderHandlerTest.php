<?php

use App\Application\Order\Commands\CreateOrder;
use App\Application\Order\Handlers\CreateOrderHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\Order\Events\OrderCreated;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists an OrderCreated event via the handler', function () {
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

    $handler = new CreateOrderHandler($fakeStore, $fakeDispatcher);

    $command = new CreateOrder(
        orderUuid: 'order-uuid-123',
        patientId: 42,
        doctorId: 7,
        prescriptionId: 99,
        patientNotes: 'Patient reported headache',
        doctorNotes: 'Review labs before fulfilling',
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(OrderCreated::class)
        ->and($event->aggregateUuid)->toBe('order-uuid-123')
        ->and($event->payload)->toBe([
            'patient_id' => 42,
            'doctor_id' => 7,
            'prescription_id' => 99,
            'status' => 'pending',
            'patient_notes' => 'Patient reported headache',
            'doctor_notes' => 'Review labs before fulfilling',
        ])
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

