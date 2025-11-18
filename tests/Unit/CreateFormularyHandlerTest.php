<?php

use App\Application\MedicationCatalog\Commands\CreateFormulary;
use App\Application\MedicationCatalog\Handlers\CreateFormularyHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\FormularyCreated;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a FormularyCreated event via the handler', function () {
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

    $handler = new CreateFormularyHandler($fakeStore, $fakeDispatcher);

    $command = new CreateFormulary(
        formularyUuid: 'form-uuid-123',
        name: 'Standard Formulary',
        description: 'Standard medication formulary for all patients',
        organizationId: 'org-123',
        type: 'standard',
        status: 'active',
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(FormularyCreated::class)
        ->and($event->aggregateUuid)->toBe('form-uuid-123')
        ->and($event->payload['name'])->toBe('Standard Formulary')
        ->and($event->payload['organization_id'])->toBe('org-123')
        ->and($event->payload['status'])->toBe('active')
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

