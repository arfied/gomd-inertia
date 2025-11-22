<?php

namespace App\Application\Signup\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for processing payment during signup.
 */
class ProcessPayment implements Command
{
    public function __construct(
        public string $signupId,
        public string $paymentId,
        public float $amount,
        public string $status, // success, pending, failed
        public array $metadata = [],
    ) {
    }
}

