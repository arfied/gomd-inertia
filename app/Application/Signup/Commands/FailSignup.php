<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for failing a signup process.
 */
class FailSignup implements Command
{
    public function __construct(
        public string $signupId,
        public string $reason, // validation_error, payment_failed, system_error
        public string $message,
        public array $metadata = [],
    ) {
    }
}

