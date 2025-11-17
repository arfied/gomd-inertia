<?php

namespace App\Application\Order\Commands;

use App\Domain\Shared\Commands\Command;

class AssignOrderToDoctor implements Command
{
    public function __construct(
        public string $orderUuid,
        public int $doctorId,
        public ?int $assignedByUserId = null,
        public array $metadata = [],
    ) {
    }
}

