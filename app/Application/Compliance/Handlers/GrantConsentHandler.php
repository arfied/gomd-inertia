<?php

namespace App\Application\Compliance\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Compliance\Commands\GrantConsent;
use App\Domain\Compliance\ConsentAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class GrantConsentHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof GrantConsent) {
            throw new InvalidArgumentException('GrantConsentHandler can only handle GrantConsent commands');
        }

        $payload = [
            'patient_id' => $command->patientId,
            'consent_type' => $command->consentType,
            'granted_by' => $command->grantedBy,
            'granted_at' => $command->grantedAt ?? now()->toIso8601String(),
            'expires_at' => $command->expiresAt,
            'terms_version' => $command->termsVersion,
            'status' => 'active',
        ];

        $consent = ConsentAggregate::create(
            $command->consentUuid,
            $payload,
            $command->metadata,
        );

        foreach ($consent->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

