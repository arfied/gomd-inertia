<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\RecordPatientMedication;
use App\Domain\Patient\Events\PatientMedicationAdded;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class RecordPatientMedicationHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof RecordPatientMedication) {
            throw new InvalidArgumentException('RecordPatientMedicationHandler can only handle RecordPatientMedication commands');
        }

        $payload = [
            'user_id' => $command->userId,
            'medication_id' => $command->medicationId,
            'start_date' => $command->startDate ?? now()->toDateString(),
            'end_date' => $command->endDate,
            'dosage' => $command->dosage,
            'frequency' => $command->frequency,
            'notes' => $command->notes,
        ];

        $event = new PatientMedicationAdded(
            $command->patientUuid,
            $payload,
            $command->metadata,
        );

        $this->eventStore->store($event);
        $this->events->dispatch($event);
    }
}

