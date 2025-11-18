<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for adding a medication to a formulary.
 */
class AddMedicationToFormulary implements Command
{
    public function __construct(
        public string $formularyUuid,
        public string $medicationUuid,
        public ?string $tier = null,
        public ?bool $requiresPreAuthorization = null,
        public ?string $notes = null,
        public array $metadata = [],
    ) {
    }
}

