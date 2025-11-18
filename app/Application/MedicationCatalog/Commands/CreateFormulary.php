<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a formulary.
 *
 * A formulary is a curated list of approved medications for a specific
 * organization, insurance plan, or clinical protocol.
 */
class CreateFormulary implements Command
{
    public function __construct(
        public string $formularyUuid,
        public string $name,
        public ?string $description = null,
        public ?string $organizationId = null,
        public ?string $type = null,
        public string $status = 'active',
        public array $metadata = [],
    ) {
    }
}

