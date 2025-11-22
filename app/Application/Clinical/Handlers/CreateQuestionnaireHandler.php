<?php

namespace App\Application\Clinical\Handlers;

use App\Application\Clinical\Commands\CreateQuestionnaire;
use App\Application\Commands\CommandHandler;
use App\Domain\Clinical\QuestionnaireAggregate;
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

        $payload = [
            'title' => $command->title,
            'description' => $command->description,
            'questions' => $command->questions ?? [],
            'created_by' => $command->createdBy,
            'patient_id' => $command->patientId,
            'created_at' => now()->toIso8601String(),
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

