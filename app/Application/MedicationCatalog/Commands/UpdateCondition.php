<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for updating a condition in the catalog.
 */
class UpdateCondition implements Command
{
    public function __construct(
        public string $conditionUuid,
        public ?string $name = null,
        public ?string $therapeuticUse = null,
        public ?string $slug = null,
        public ?string $description = null,
        public array $metadata = [],
    ) {
    }
}

