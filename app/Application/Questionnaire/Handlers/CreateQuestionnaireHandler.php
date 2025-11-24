<?php

namespace App\Application\Questionnaire\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Questionnaire\Commands\CreateQuestionnaire;
use App\Domain\Questionnaire\QuestionnaireAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class CreateQuestionnaireHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof CreateQuestionnaire) {
            throw new InvalidArgumentException('CreateQuestionnaireHandler can only handle CreateQuestionnaire commands');
        }

        $questionnaire = QuestionnaireAggregate::create(
            $command->questionnaireId,
            $command->title,
            $command->description,
            $command->questions,
            $command->conditionId,
            $command->metadata,
        );

        foreach ($questionnaire->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

