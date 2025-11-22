<?php

namespace App\Application\Compliance\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Compliance\Commands\LogDataAccess;
use App\Domain\Compliance\AuditLogAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class LogDataAccessHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof LogDataAccess) {
            throw new InvalidArgumentException('LogDataAccessHandler can only handle LogDataAccess commands');
        }

        $payload = [
            'patient_id' => $command->patientId,
            'accessed_by' => $command->accessedBy,
            'access_type' => $command->accessType,
            'resource' => $command->resource,
            'accessed_at' => $command->accessedAt ?? now()->toIso8601String(),
            'ip_address' => $command->ipAddress,
            'user_agent' => $command->userAgent,
        ];

        $auditLog = AuditLogAggregate::create(
            $command->auditLogUuid,
            $payload,
            $command->metadata,
        );

        foreach ($auditLog->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

