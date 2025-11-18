<?php

namespace App\Application\MedicationCatalog\Queries;

use App\Application\MedicationCatalog\FormularyFinder;
use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use Illuminate\Pagination\Paginator;
use InvalidArgumentException;

class SearchFormulariesHandler implements QueryHandler
{
    public function __construct(
        private FormularyFinder $finder,
    ) {
    }

    /**
     * @return Paginator
     */
    public function handle(Query $query): Paginator
    {
        if (! $query instanceof SearchFormularies) {
            throw new InvalidArgumentException('SearchFormulariesHandler can only handle SearchFormularies queries.');
        }

        return $this->finder->search(
            query: $query->query,
            organizationId: $query->organizationId,
            type: $query->type,
            status: $query->status,
            page: $query->page,
            perPage: $query->perPage,
            sortBy: $query->sortBy,
            sortOrder: $query->sortOrder,
        );
    }
}

