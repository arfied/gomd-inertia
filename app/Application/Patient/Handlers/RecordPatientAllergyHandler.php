<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\RecordPatientAllergy;
use App\Domain\Patient\Events\PatientAllergyRecorded;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class RecordPatientAllergyHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof RecordPatientAllergy) {
            throw new InvalidArgumentException('RecordPatientAllergyHandler can only handle RecordPatientAllergy commands');
        }

        $payload = [
            'user_id' => $command->userId,
            'allergen' => $command->allergen,
            'reaction' => $command->reaction,
            'severity' => $command->severity,
            'notes' => $command->notes,
        ];

        $event = new PatientAllergyRecorded(
            $command->patientUuid,
            $payload,
            $command->metadata,
        );

        $this->eventStore->store($event);
        $this->events->dispatch($event);
    }
}

