<?php

namespace App\Application\MedicationCatalog\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for updating a medication in the catalog.
 */
class UpdateMedication implements Command
{
    public function __construct(
        public string $medicationUuid,
        public ?string $name = null,
        public ?string $genericName = null,
        public ?string $description = null,
        public ?string $dosageForm = null,
        public ?string $strength = null,
        public ?string $manufacturer = null,
        public ?string $ndcNumber = null,
        public ?float $unitPrice = null,
        public ?bool $requiresPrescription = null,
        public ?bool $controlledSubstance = null,
        public ?string $storageConditions = null,
        public ?string $type = null,
        public ?string $drugClass = null,
        public ?string $routeOfAdministration = null,
        public ?string $halfLife = null,
        public ?string $contraindications = null,
        public ?string $sideEffects = null,
        public ?string $interactions = null,
        public ?string $pregnancyCategory = null,
        public ?bool $breastfeedingSafe = null,
        public ?string $blackBoxWarning = null,
        public ?string $status = null,
        public array $metadata = [],
    ) {
    }
}

