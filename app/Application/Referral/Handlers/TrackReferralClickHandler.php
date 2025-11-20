<?php

namespace App\Application\Referral\Handlers;

use App\Application\Referral\Commands\TrackReferralClick;
use App\Domain\Referral\Events\ReferralLinkClicked;
use App\Models\ReferralLink;
use App\Models\ReferralClick;

/**
 * TrackReferralClickHandler
 *
 * Handles tracking of referral link clicks.
 */
class TrackReferralClickHandler
{
    public function handle(TrackReferralClick $command): void
    {
        $referralLink = ReferralLink::where('referral_code', $command->referralCode)->first();

        if (!$referralLink) {
            return;
        }

        // Record the click
        ReferralClick::create([
            'referral_link_id' => $referralLink->id,
            'ip_address' => $command->ipAddress,
            'user_agent' => $command->userAgent,
            'referrer_url' => $command->referrerUrl,
            'session_id' => $command->sessionId,
        ]);

        // Update referral link click count
        $referralLink->recordClick();

        // Dispatch event
        event(new ReferralLinkClicked(
            aggregateUuid: $referralLink->referral_token,
            payload: [
                'referral_link_id' => $referralLink->id,
                'agent_id' => $referralLink->agent_id,
                'referral_type' => $referralLink->referral_type,
                'clicks_count' => $referralLink->clicks_count,
            ],
        ));
    }
}

