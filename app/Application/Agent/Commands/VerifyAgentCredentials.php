<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * VerifyAgentCredentials Command
 *
 * Part of the Agent Onboarding Saga.
 * Verifies agent credentials and triggers license checking.
 */
class VerifyAgentCredentials implements Command
{
    public function __construct(
        public string $agentUuid,
        public string $sagaUuid,
        public array $credentials,
        public array $metadata = [],
    ) {
    }
}

