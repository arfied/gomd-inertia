<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for updating a formulary.
 */
class UpdateFormulary implements Command
{
    public function __construct(
        public string $formularyUuid,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $organizationId = null,
        public ?string $type = null,
        public ?string $status = null,
        public array $metadata = [],
    ) {
    }
}

