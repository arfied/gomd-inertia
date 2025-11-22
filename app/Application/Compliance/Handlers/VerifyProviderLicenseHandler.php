<?php

namespace App\Application\Compliance\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Compliance\Commands\VerifyProviderLicense;
use App\Domain\Compliance\LicenseAggregate;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

class VerifyProviderLicenseHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof VerifyProviderLicense) {
            throw new InvalidArgumentException('VerifyProviderLicenseHandler can only handle VerifyProviderLicense commands');
        }

        $payload = [
            'provider_id' => $command->providerId,
            'license_number' => $command->licenseNumber,
            'license_type' => $command->licenseType,
            'verified_at' => $command->verifiedAt ?? now()->toIso8601String(),
            'expires_at' => $command->expiresAt,
            'issuing_body' => $command->issuingBody,
            'verification_url' => $command->verificationUrl,
            'status' => 'verified',
        ];

        $license = LicenseAggregate::create(
            $command->licenseUuid,
            $payload,
            $command->metadata,
        );

        foreach ($license->releaseEvents() as $event) {
            $this->eventStore->store($event);
            $this->events->dispatch($event);
        }
    }
}

