<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a subscription after successful payment.
 */
class CreateSubscription implements Command
{
    public function __construct(
        public string $signupId,
        public string $subscriptionId,
        public string $userId,
        public string $planId,
        public ?string $medicationId = null,
        public ?string $conditionId = null,
        public array $metadata = [],
    ) {
    }
}

