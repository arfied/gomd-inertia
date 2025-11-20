<?php

namespace App\Application\Agent\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * RegisterAgent Command
 *
 * Initiates the Agent Onboarding Saga.
 * Triggers credential verification and license checking.
 */
class RegisterAgent implements Command
{
    public function __construct(
        public string $agentUuid,
        public int $userId,
        public string $tier,
        public ?string $referringAgentId = null,
        public array $metadata = [],
    ) {
    }
}

