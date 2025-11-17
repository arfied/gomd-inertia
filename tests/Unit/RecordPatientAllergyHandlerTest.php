<?php

use App\Application\Patient\Commands\RecordPatientAllergy;
use App\Application\Patient\Handlers\RecordPatientAllergyHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\Patient\Events\PatientAllergyRecorded;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;

it('persists a PatientAllergyRecorded event via the handler', function () {
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

    $handler = new RecordPatientAllergyHandler($fakeStore, $fakeDispatcher);

    $command = new RecordPatientAllergy(
        patientUuid: 'patient-uuid-123',
        userId: 42,
        allergen: 'Peanuts',
        reaction: 'Anaphylaxis',
        severity: 'severe',
        notes: 'Carry EpiPen',
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1)
        ->and($fakeDispatcher->dispatched)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(PatientAllergyRecorded::class)
        ->and($event->aggregateUuid)->toBe('patient-uuid-123')
        ->and($event->payload)->toBe([
            'user_id' => 42,
            'allergen' => 'Peanuts',
            'reaction' => 'Anaphylaxis',
            'severity' => 'severe',
            'notes' => 'Carry EpiPen',
        ])
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

