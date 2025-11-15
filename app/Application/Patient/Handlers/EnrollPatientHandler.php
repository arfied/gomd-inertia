<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\EnrollPatient;
use App\Domain\Events\DomainEvent;
use App\Domain\Patient\Events\PatientEnrolled;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

/**
 * Command handler that persists a PatientEnrolled event.
 *
 * For now this is a minimal example that does not yet coordinate with
 * existing Eloquent models. It simply writes an event into the event_store
 * table via the EventStore service.
 */
class EnrollPatientHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof EnrollPatient) {
            throw new InvalidArgumentException('EnrollPatientHandler can only handle EnrollPatient commands.');
        }

        $event = new PatientEnrolled(
            $command->patientUuid,
            [
                'user_id' => $command->userId,
            ],
            $command->metadata,
        );

        $this->eventStore->store($event);

        $this->events->dispatch($event);
    }
}

