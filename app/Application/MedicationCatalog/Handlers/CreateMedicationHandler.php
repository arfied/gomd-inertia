<?php

namespace App\Application\MedicationCatalog\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\MedicationCatalog\Commands\CreateMedication;
use App\Domain\MedicationCatalog\MedicationAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreateMedicationHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreateMedication) {
            throw new InvalidArgumentException('CreateMedicationHandler can only handle CreateMedication commands');
        }

        $payload = [
            'name' => $command->name,
            'generic_name' => $command->genericName,
            'description' => $command->description,
            'dosage_form' => $command->dosageForm,
            'strength' => $command->strength,
            'manufacturer' => $command->manufacturer,
            'ndc_number' => $command->ndcNumber,
            'unit_price' => $command->unitPrice,
            'requires_prescription' => $command->requiresPrescription,
            'controlled_substance' => $command->controlledSubstance,
            'storage_conditions' => $command->storageConditions,
            'type' => $command->type,
            'drug_class' => $command->drugClass,
            'route_of_administration' => $command->routeOfAdministration,
            'half_life' => $command->halfLife,
            'contraindications' => $command->contraindications,
            'side_effects' => $command->sideEffects,
            'interactions' => $command->interactions,
            'pregnancy_category' => $command->pregnancyCategory,
            'breastfeeding_safe' => $command->breastfeedingSafe,
            'black_box_warning' => $command->blackBoxWarning,
            'status' => $command->status,
        ];

        $medication = MedicationAggregate::create(
            $command->medicationUuid,
            $payload,
            $command->metadata,
        );

        foreach ($medication->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

