<?php

namespace App\Application\Clinical\Handlers;

use App\Application\Clinical\Commands\SubmitQuestionnaireResponse;
use App\Application\Commands\CommandHandler;
use App\Domain\Clinical\QuestionnaireAggregate;
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

        $payload = [
            'questionnaire_id' => $command->questionnaireUuid,
            'patient_id' => $command->patientId,
            'responses' => $command->responses,
            'submitted_at' => $command->submittedAt ?? now()->toIso8601String(),
        ];

        $questionnaire = QuestionnaireAggregate::create(
            $command->questionnaireUuid,
            $payload,
            $command->metadata,
        );

        foreach ($questionnaire->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

