<?php

namespace App\Application\MedicationCatalog\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\MedicationCatalog\Commands\UpdateFormulary;
use App\Domain\MedicationCatalog\FormularyAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class UpdateFormularyHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof UpdateFormulary) {
            throw new InvalidArgumentException('UpdateFormularyHandler can only handle UpdateFormulary commands');
        }

        $payload = array_filter([
            'name' => $command->name,
            'description' => $command->description,
            'organization_id' => $command->organizationId,
            'type' => $command->type,
            'status' => $command->status,
        ], fn ($value) => $value !== null);

        $formulary = FormularyAggregate::update(
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

