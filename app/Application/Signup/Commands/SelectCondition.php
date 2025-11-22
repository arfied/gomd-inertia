<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for selecting a condition during signup.
 */
class SelectCondition implements Command
{
    public function __construct(
        public string $signupId,
        public string $conditionId,
        public array $metadata = [],
    ) {
    }
}

