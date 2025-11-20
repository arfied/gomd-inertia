<?php

namespace App\Application\Referral\Handlers;

use App\Application\Referral\Commands\RecordReferralConversion;
use App\Domain\Referral\Events\ReferralConverted;
use App\Models\ReferralLink;
use App\Models\ReferralClick;

/**
 * RecordReferralConversionHandler
 *
 * Handles recording of referral conversions.
 */
class RecordReferralConversionHandler
{
    public function handle(RecordReferralConversion $command): void
    {
        $referralLink = ReferralLink::where('referral_code', $command->referralCode)->first();

        if (!$referralLink) {
            return;
        }

        // Find the most recent unconverted click for this referral link
        $click = ReferralClick::forReferralLink($referralLink->id)
            ->unconverted()
            ->latest()
            ->first();

        if ($click) {
            $click->markAsConverted();
        }

        // Update referral link conversion count
        $referralLink->recordConversion();

        // Dispatch event
        event(new ReferralConverted(
            aggregateUuid: $referralLink->referral_token,
            payload: [
                'referral_link_id' => $referralLink->id,
                'agent_id' => $referralLink->agent_id,
                'referral_type' => $referralLink->referral_type,
                'converted_entity_id' => $command->convertedEntityId,
                'converted_entity_type' => $command->convertedEntityType,
                'conversions_count' => $referralLink->conversions_count,
                'conversion_rate' => $referralLink->conversion_rate,
            ],
        ));
    }
}

