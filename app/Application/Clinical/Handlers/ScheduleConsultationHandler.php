<?php

namespace App\Application\Clinical\Handlers;

use App\Application\Clinical\Commands\ScheduleConsultation;
use App\Application\Commands\CommandHandler;
use App\Domain\Clinical\ConsultationAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class ScheduleConsultationHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof ScheduleConsultation) {
            throw new InvalidArgumentException('ScheduleConsultationHandler can only handle ScheduleConsultation commands');
        }

        $payload = [
            'patient_id' => $command->patientId,
            'doctor_id' => $command->doctorId,
            'scheduled_at' => $command->scheduledAt,
            'reason' => $command->reason,
            'notes' => $command->notes,
            'status' => 'scheduled',
        ];

        $consultation = ConsultationAggregate::create(
            $command->consultationUuid,
            $payload,
            $command->metadata,
        );

        foreach ($consultation->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

