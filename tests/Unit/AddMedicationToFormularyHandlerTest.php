<?php

use App\Application\MedicationCatalog\Commands\AddMedicationToFormulary;
use App\Application\MedicationCatalog\Handlers\AddMedicationToFormularyHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a MedicationAddedToFormulary event via the handler', function () {
    $fakeStore = new class implements EventStoreContract {
        /** @var array<int, DomainEvent> */
        public array $stored = [];

        public function store(DomainEvent $event): StoredEvent
        {
            $this->stored[] = $event;

            return new class extends StoredEvent {
                public function __construct() {}
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

    $handler = new AddMedicationToFormularyHandler($fakeStore, $fakeDispatcher);

    $command = new AddMedicationToFormulary(
        formularyUuid: 'form-uuid-123',
        medicationUuid: 'med-uuid-456',
        tier: 'preferred',
        requiresPreAuthorization: false,
        notes: 'First-line treatment',
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(MedicationAddedToFormulary::class)
        ->and($event->aggregateUuid)->toBe('form-uuid-123')
        ->and($event->payload['medication_uuid'])->toBe('med-uuid-456')
        ->and($event->payload['tier'])->toBe('preferred')
        ->and($event->payload['requires_pre_authorization'])->toBe(false)
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

