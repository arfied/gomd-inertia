<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * CheckAgentLicense Command
 *
 * Part of the Agent Onboarding Saga.
 * Checks agent license and triggers referral link creation.
 */
class CheckAgentLicense implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public string $licenseNumber,
        public array $metadata = [],
    ) {
    }
}

