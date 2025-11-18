<?php

use App\Application\MedicationCatalog\Commands\CreateMedication;
use App\Application\MedicationCatalog\Handlers\CreateMedicationHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\MedicationCreated;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a MedicationCreated event via the handler', function () {
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

        public function listen($events, $listener = null): void {}
        public function hasListeners($eventName): bool { return false; }
        public function subscribe($subscriber): void {}
        public function until($event, $payload = []) { return null; }
        public function dispatch($event, $payload = [], $halt = false) {
            $this->dispatched[] = $event;
            return null;
        }
        public function push($event, $payload = []): void {}
        public function flush($event): void {}
        public function forget($event): void {}
        public function forgetPushed(): void {}
    };

    $handler = new CreateMedicationHandler($fakeStore, $fakeDispatcher);

    $command = new CreateMedication(
        medicationUuid: 'med-uuid-123',
        name: 'Aspirin',
        genericName: 'Acetylsalicylic acid',
        description: 'Pain reliever and fever reducer',
        dosageForm: 'Tablet',
        strength: '500mg',
        manufacturer: 'Bayer',
        ndcNumber: '0025-1125-01',
        unitPrice: 5.99,
        requiresPrescription: false,
        controlledSubstance: false,
        storageConditions: 'Room temperature',
        type: 'OTC',
        drugClass: 'NSAID',
        status: 'active',
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(MedicationCreated::class)
        ->and($event->aggregateUuid)->toBe('med-uuid-123')
        ->and($event->payload['name'])->toBe('Aspirin')
        ->and($event->payload['generic_name'])->toBe('Acetylsalicylic acid')
        ->and($event->payload['unit_price'])->toBe(5.99)
        ->and($event->payload['status'])->toBe('active')
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

