<?php

use App\Application\MedicationCatalog\Commands\CreateCondition;
use App\Application\MedicationCatalog\Handlers\CreateConditionHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\MedicationCatalog\Events\ConditionCreated;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a ConditionCreated event via the handler', function () {
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

    $handler = new CreateConditionHandler($fakeStore, $fakeDispatcher);

    $command = new CreateCondition(
        conditionUuid: 'cond-uuid-123',
        name: 'Hypertension',
        therapeuticUse: 'Blood pressure management',
        slug: 'hypertension',
        description: 'High blood pressure condition',
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(ConditionCreated::class)
        ->and($event->aggregateUuid)->toBe('cond-uuid-123')
        ->and($event->payload['name'])->toBe('Hypertension')
        ->and($event->payload['therapeutic_use'])->toBe('Blood pressure management')
        ->and($event->payload['slug'])->toBe('hypertension')
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

