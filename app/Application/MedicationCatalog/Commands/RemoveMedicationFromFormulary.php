<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for removing a medication from a formulary.
 */
class RemoveMedicationFromFormulary implements Command
{
    public function __construct(
        public string $formularyUuid,
        public string $medicationUuid,
        public ?string $reason = null,
        public array $metadata = [],
    ) {
    }
}

