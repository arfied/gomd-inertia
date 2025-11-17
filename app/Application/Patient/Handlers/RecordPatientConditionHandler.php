<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\RecordPatientCondition;
use App\Domain\Patient\Events\PatientConditionRecorded;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class RecordPatientConditionHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof RecordPatientCondition) {
            throw new InvalidArgumentException('RecordPatientConditionHandler can only handle RecordPatientCondition commands');
        }

        $payload = [
            'patient_id' => $command->userId,
            'condition_name' => $command->conditionName,
            'diagnosed_at' => $command->diagnosedAt,
            'notes' => $command->notes,
            'had_condition_before' => $command->hadConditionBefore,
            'is_chronic' => $command->isChronic,
        ];

        $event = new PatientConditionRecorded(
            $command->patientUuid,
            $payload,
            $command->metadata,
        );

        $this->eventStore->store($event);
        $this->events->dispatch($event);
    }
}

