<?php

namespace App\Application\Referral\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * RecordReferralConversion Command
 *
 * Records a conversion for a referral link.
 */
class RecordReferralConversion implements Command
{
    public function __construct(
        public string $referralCode,
        public int $convertedEntityId,
        public string $convertedEntityType,
    ) {
    }
}

