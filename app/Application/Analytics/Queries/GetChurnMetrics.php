<?php

namespace App\Application\Analytics\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * Query to get churn metrics.
 *
 * Churn rate is the percentage of customers who cancel their subscriptions
 * in a given period. This query returns churn rate, churn count, and reasons.
 */
class GetChurnMetrics implements Query
{
    public function __construct(
        public ?string $month = null, // 'YYYY-MM' format, defaults to current month
        public bool $includeReasons = true, // Include churn reasons breakdown
        public bool $includeTrend = true, // Include previous months for trend
        public int $trendMonths = 12, // Number of months to include in trend
    ) {
    }
}

