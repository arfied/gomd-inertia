<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

class RecordPatientCondition implements Command
{
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public string $conditionName,
        public ?string $diagnosedAt = null,
        public ?string $notes = null,
        public ?bool $hadConditionBefore = null,
        public ?bool $isChronic = null,
        public array $metadata = [],
    ) {
    }
}

