<?php

namespace App\Application\Clinical\Handlers;

use App\Application\Clinical\Commands\RecordClinicalNote;
use App\Application\Commands\CommandHandler;
use App\Domain\Clinical\ClinicalNoteAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class RecordClinicalNoteHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof RecordClinicalNote) {
            throw new InvalidArgumentException('RecordClinicalNoteHandler can only handle RecordClinicalNote commands');
        }

        $payload = [
            'patient_id' => $command->patientId,
            'doctor_id' => $command->doctorId,
            'note_type' => $command->noteType,
            'content' => $command->content,
            'attachments' => $command->attachments ?? [],
            'recorded_at' => $command->recordedAt ?? now()->toIso8601String(),
        ];

        $clinicalNote = ClinicalNoteAggregate::create(
            $command->clinicalNoteUuid,
            $payload,
            $command->metadata,
        );

        foreach ($clinicalNote->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

