<?php

use App\Application\Patient\Commands\EnrollPatient;
use App\Application\Patient\Handlers\EnrollPatientHandler;
use App\Domain\Events\DomainEvent;
use App\Domain\Patient\Events\PatientEnrolled;
use App\Models\StoredEvent;
use App\Services\EventStoreContract;

it('persists a PatientEnrolled event via the handler', function () {
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

    $handler = new EnrollPatientHandler($fakeStore);

    $command = new EnrollPatient(
        patientUuid: 'patient-uuid-123',
        userId: 42,
        metadata: ['source' => 'test'],
    );

    $handler->handle($command);

    expect($fakeStore->stored)->toHaveCount(1);

    $event = $fakeStore->stored[0];

    expect($event)->toBeInstanceOf(PatientEnrolled::class)
        ->and($event->aggregateUuid)->toBe('patient-uuid-123')
        ->and($event->payload)->toBe(['user_id' => 42])
        ->and($event->metadata)->toMatchArray(['source' => 'test']);
});

