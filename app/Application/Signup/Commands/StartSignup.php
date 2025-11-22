<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for starting a signup flow.
 *
 * Initiates a new signup process with a specific path:
 * - medication_first: Select medication → plan → questionnaire
 * - condition_first: Select condition → plan → questionnaire
 * - plan_first: Select plan → payment → medication/condition
 */
class StartSignup implements Command
{
    public function __construct(
        public string $signupId,
        public string $userId,
        public string $signupPath, // medication_first, condition_first, plan_first
        public array $metadata = [],
    ) {
    }
}

