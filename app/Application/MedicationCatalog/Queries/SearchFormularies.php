<?php

namespace App\Application\MedicationCatalog\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * Query DTO for searching formularies.
 */
class SearchFormularies implements Query
{
    public function __construct(
        public ?string $query = null,
        public ?string $organizationId = null,
        public ?string $type = null,
        public string $status = 'active',
        public int $page = 1,
        public int $perPage = 15,
        public string $sortBy = 'name',
        public string $sortOrder = 'asc',
    ) {
    }
}

