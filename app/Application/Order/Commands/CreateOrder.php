<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

class CreateOrder implements Command
{
    public function __construct(
        public string $orderUuid,
        public int $patientId,
        public ?int $doctorId = null,
        public ?int $prescriptionId = null,
        public ?string $patientNotes = null,
        public ?string $doctorNotes = null,
        public array $metadata = [],
    ) {
    }
}

