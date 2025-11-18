<?php

namespace App\Application\MedicationCatalog\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\MedicationCatalog\Commands\AddMedicationToFormulary;
use App\Domain\MedicationCatalog\FormularyAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class AddMedicationToFormularyHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof AddMedicationToFormulary) {
            throw new InvalidArgumentException('AddMedicationToFormularyHandler can only handle AddMedicationToFormulary commands');
        }

        $payload = [
            'medication_uuid' => $command->medicationUuid,
            'tier' => $command->tier,
            'requires_pre_authorization' => $command->requiresPreAuthorization,
            'notes' => $command->notes,
        ];

        $formulary = FormularyAggregate::addMedication(
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

