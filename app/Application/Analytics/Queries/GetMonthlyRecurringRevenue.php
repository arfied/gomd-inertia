<?php

namespace App\Application\Analytics\Queries;

use App\Domain\Shared\Queries\Query;

/**
 * Query to get Monthly Recurring Revenue (MRR) metrics.
 *
 * MRR is the predictable revenue from active subscriptions in a given month.
 * This query returns current MRR, previous month MRR, and trend analysis.
 */
class GetMonthlyRecurringRevenue implements Query
{
    public function __construct(
        public ?string $month = null, // 'YYYY-MM' format, defaults to current month
        public bool $includeTrend = true, // Include previous months for trend
        public int $trendMonths = 12, // Number of months to include in trend
    ) {
    }
}

