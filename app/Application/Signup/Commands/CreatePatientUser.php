<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a patient user during signup flow.
 *
 * This command is dispatched when the user provides their email address,
 * triggering the creation of a new user account with the patient role.
 */
class CreatePatientUser implements Command
{
    public function __construct(
        public string $signupId,
        public string $email,
        public array $metadata = [],
    ) {
    }
}

