<?php

namespace App\Application\Commission;

use App\Domain\Commission\Events\PayoutProcessed;
use App\Domain\Commission\Events\PayoutRequested;

/**
 * Interface for Payout History projector.
 *
 * Handles projections of payout events into read models
 * optimized for payout history and tracking.
 */
interface PayoutHistoryProjector
{
    public function projectPayoutRequested(PayoutRequested $event): void;

    public function projectPayoutProcessed(PayoutProcessed $event): void;
}

