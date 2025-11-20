<?php

namespace App\Application\Referral\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * TrackReferralClick Command
 *
 * Tracks a click on a referral link.
 */
class TrackReferralClick implements Command
{
    public function __construct(
        public string $referralCode,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?string $referrerUrl = null,
        public ?string $sessionId = null,
    ) {
    }
}

