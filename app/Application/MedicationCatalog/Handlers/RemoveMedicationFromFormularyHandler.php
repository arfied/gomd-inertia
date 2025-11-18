<?php

namespace App\Application\MedicationCatalog\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\MedicationCatalog\Commands\RemoveMedicationFromFormulary;
use App\Domain\MedicationCatalog\FormularyAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class RemoveMedicationFromFormularyHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof RemoveMedicationFromFormulary) {
            throw new InvalidArgumentException('RemoveMedicationFromFormularyHandler can only handle RemoveMedicationFromFormulary commands');
        }

        $payload = [
            'medication_uuid' => $command->medicationUuid,
            'reason' => $command->reason,
        ];

        $formulary = FormularyAggregate::removeMedication(
            $command->formularyUuid,
            $payload,
            $command->metadata,
        );

        foreach ($formulary->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

