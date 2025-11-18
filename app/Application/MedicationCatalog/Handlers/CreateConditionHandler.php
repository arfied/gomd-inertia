<?php

namespace App\Application\MedicationCatalog\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\MedicationCatalog\Commands\CreateCondition;
use App\Domain\MedicationCatalog\ConditionAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreateConditionHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreateCondition) {
            throw new InvalidArgumentException('CreateConditionHandler can only handle CreateCondition commands');
        }

        $payload = [
            'name' => $command->name,
            'therapeutic_use' => $command->therapeuticUse,
            'slug' => $command->slug,
            'description' => $command->description,
        ];

        $condition = ConditionAggregate::create(
            $command->conditionUuid,
            $payload,
            $command->metadata,
        );

        foreach ($condition->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

