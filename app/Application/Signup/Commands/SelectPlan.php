<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for selecting a plan during signup.
 */
class SelectPlan implements Command
{
    public function __construct(
        public string $signupId,
        public string $planId,
        public array $metadata = [],
    ) {
    }
}

