<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\RecordPatientVisitSummary;
use App\Domain\Patient\Events\PatientVisitSummaryRecorded;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class RecordPatientVisitSummaryHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof RecordPatientVisitSummary) {
            throw new InvalidArgumentException('RecordPatientVisitSummaryHandler can only handle RecordPatientVisitSummary commands');
        }

        $payload = [
            'patient_id' => $command->userId,
            'past_injuries' => $command->pastInjuries,
            'past_injuries_details' => $command->pastInjuriesDetails,
            'surgery' => $command->surgery,
            'surgery_details' => $command->surgeryDetails,
            'chronic_conditions_details' => $command->chronicConditionsDetails,
            'chronic_pain' => $command->chronicPain,
            'chronic_pain_details' => $command->chronicPainDetails,
            'family_history_conditions' => $command->familyHistoryConditions,
        ];

        $event = new PatientVisitSummaryRecorded(
            $command->patientUuid,
            $payload,
            $command->metadata,
        );

        $this->eventStore->store($event);
        $this->events->dispatch($event);
    }
}

