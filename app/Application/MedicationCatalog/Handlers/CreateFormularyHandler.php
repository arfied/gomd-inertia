<?php

namespace App\Application\MedicationCatalog\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\MedicationCatalog\Commands\CreateFormulary;
use App\Domain\MedicationCatalog\FormularyAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreateFormularyHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreateFormulary) {
            throw new InvalidArgumentException('CreateFormularyHandler can only handle CreateFormulary commands');
        }

        $payload = [
            'name' => $command->name,
            'description' => $command->description,
            'organization_id' => $command->organizationId,
            'type' => $command->type,
            'status' => $command->status,
        ];

        $formulary = FormularyAggregate::create(
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

