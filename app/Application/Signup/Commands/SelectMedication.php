<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for selecting a medication during signup.
 */
class SelectMedication implements Command
{
    public function __construct(
        public string $signupId,
        public string $medicationName,
        public array $metadata = [],
    ) {
    }
}

