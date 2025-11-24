<?php

namespace App\Application\Questionnaire\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Questionnaire\Commands\SubmitQuestionnaireResponse;
use App\Domain\Questionnaire\QuestionnaireAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class SubmitQuestionnaireResponseHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof SubmitQuestionnaireResponse) {
            throw new InvalidArgumentException('SubmitQuestionnaireResponseHandler can only handle SubmitQuestionnaireResponse commands');
        }

        $questionnaire = QuestionnaireAggregate::fromEventStream($command->questionnaireId);

        $questionnaire->submitResponse(
            $command->patientId,
            $command->responses,
            $command->metadata,
        );

        foreach ($questionnaire->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

