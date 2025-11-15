<?php

namespace App\Application\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * A query handler executes a single query against the read model
 * and returns data (arrays, DTOs, or value objects).
 */
interface QueryHandler
{
    /**
     * Handle the given query and return a result.
     */
    public function handle(Query $query): mixed;
}

