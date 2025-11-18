<?php

namespace App\Application\MedicationCatalog\Queries;

use App\Application\MedicationCatalog\MedicationSearchFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use Illuminate\Pagination\Paginator;
use InvalidArgumentException;

class SearchMedicationsHandler implements QueryHandler
{
    public function __construct(
        private MedicationSearchFinder $finder,
    ) {
    }

    /**
     * @return Paginator
     */
    public function handle(Query $query): Paginator
    {
        if (! $query instanceof SearchMedications) {
            throw new InvalidArgumentException('SearchMedicationsHandler can only handle SearchMedications queries.');
        }

        return $this->finder->search(
            query: $query->query,
            drugClass: $query->drugClass,
            requiresPrescription: $query->requiresPrescription,
            controlledSubstance: $query->controlledSubstance,
            status: $query->status,
            page: $query->page,
            perPage: $query->perPage,
            sortBy: $query->sortBy,
            sortOrder: $query->sortOrder,
        );
    }
}

