<?php

namespace App\Application\Signup\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Signup\Commands\CompleteQuestionnaire;
use App\Domain\Shared\Commands\Command;
use App\Domain\Signup\SignupAggregate;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CompleteQuestionnaireHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CompleteQuestionnaire) {
            throw new InvalidArgumentException('CompleteQuestionnaireHandler can only handle CompleteQuestionnaire commands');
        }

        $signup = SignupAggregate::fromEventStream($command->signupId);

        $signup->completeQuestionnaire($command->responses);

        foreach ($signup->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

