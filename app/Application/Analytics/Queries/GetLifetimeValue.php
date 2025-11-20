<?php

namespace App\Application\Analytics\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * Query to get Lifetime Value (LTV) metrics.
 *
 * LTV is the total revenue expected from a customer over their lifetime.
 * This query returns average LTV, LTV by plan, and LTV distribution.
 */
class GetLifetimeValue implements Query
{
    public function __construct(
        public ?string $month = null, // 'YYYY-MM' format, defaults to current month
        public bool $includeByPlan = true, // Include LTV breakdown by plan
        public bool $includeDistribution = true, // Include LTV distribution buckets
    ) {
    }
}

