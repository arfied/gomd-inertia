<?php

namespace App\Application\MedicationCatalog\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * Query DTO for searching medications.
 */
class SearchMedications implements Query
{
    public function __construct(
        public ?string $query = null,
        public ?string $drugClass = null,
        public ?bool $requiresPrescription = null,
        public ?bool $controlledSubstance = null,
        public string $status = 'active',
        public int $page = 1,
        public int $perPage = 15,
        public string $sortBy = 'name',
        public string $sortOrder = 'asc',
    ) {
    }
}

