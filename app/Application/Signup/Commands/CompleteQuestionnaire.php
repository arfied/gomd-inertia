<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for completing a questionnaire during signup.
 */
class CompleteQuestionnaire implements Command
{
    public function __construct(
        public string $signupId,
        public array $responses,
        public array $metadata = [],
    ) {
    }
}

