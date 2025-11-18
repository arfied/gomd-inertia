<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a condition in the catalog.
 */
class CreateCondition implements Command
{
    public function __construct(
        public string $conditionUuid,
        public string $name,
        public ?string $therapeuticUse = null,
        public ?string $slug = null,
        public ?string $description = null,
        public array $metadata = [],
    ) {
    }
}

