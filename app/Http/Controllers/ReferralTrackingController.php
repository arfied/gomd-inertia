<?php

namespace App\Http\Controllers;

use App\Application\Referral\Commands\TrackReferralClick;
use App\Application\Referral\Commands\RecordReferralConversion;
use App\Application\Referral\Handlers\TrackReferralClickHandler;
use App\Application\Referral\Handlers\RecordReferralConversionHandler;
use App\Models\ReferralLink;
use App\Models\ReferralClick;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * ReferralTrackingController
 *
 * Handles referral link tracking and conversion recording.
 */
class ReferralTrackingController extends Controller
{
    /**
     * Track a referral link click.
     */
    public function trackClick(Request $request): JsonResponse
    {
        $referralCode = $request->query('ref');

        if (!$referralCode) {
            return response()->json(['error' => 'Missing referral code'], 400);
        }

        $command = new TrackReferralClick(
            referralCode: $referralCode,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            referrerUrl: $request->header('referer'),
            sessionId: $request->getSession()->getId(),
        );

        (new TrackReferralClickHandler())->handle($command);

        return response()->json(['success' => true]);
    }

    /**
     * Record a referral conversion.
     */
    public function recordConversion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'referral_code' => 'required|string',
            'converted_entity_id' => 'required|integer',
            'converted_entity_type' => 'required|string|in:patient,agent,business',
        ]);

        $command = new RecordReferralConversion(
            referralCode: $validated['referral_code'],
            convertedEntityId: $validated['converted_entity_id'],
            convertedEntityType: $validated['converted_entity_type'],
        );

        (new RecordReferralConversionHandler())->handle($command);

        return response()->json(['success' => true]);
    }

    /**
     * Get referral link details.
     */
    public function show(string $referralCode): JsonResponse
    {
        $referralLink = ReferralLink::where('referral_code', $referralCode)->first();

        if (!$referralLink) {
            return response()->json(['error' => 'Referral link not found'], 404);
        }

        return response()->json([
            'id' => $referralLink->id,
            'agent_id' => $referralLink->agent_id,
            'referral_code' => $referralLink->referral_code,
            'referral_type' => $referralLink->referral_type,
            'clicks_count' => $referralLink->clicks_count,
            'conversions_count' => $referralLink->conversions_count,
            'conversion_rate' => $referralLink->conversion_rate,
            'status' => $referralLink->status,
        ]);
    }

    /**
     * Public landing page for referral links.
     * Tracks the click and redirects to the appropriate destination.
     */
    public function landingPage(Request $request, string $referralCode)
    {
        $referralLink = ReferralLink::where('referral_code', $referralCode)->first();

        if (!$referralLink) {
            return redirect('/')->with('error', 'Invalid referral link');
        }

        // Track the click
        ReferralClick::create([
            'referral_link_id' => $referralLink->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer_url' => $request->header('referer'),
            'session_id' => $request->getSession()->getId(),
        ]);

        // Update referral link click count
        $referralLink->recordClick();

        // Dispatch event
        event(new \App\Domain\Referral\Events\ReferralLinkClicked(
            aggregateUuid: $referralLink->referral_token,
            payload: [
                'referral_link_id' => $referralLink->id,
                'agent_id' => $referralLink->agent_id,
                'referral_type' => $referralLink->referral_type,
                'clicks_count' => $referralLink->clicks_count,
            ],
        ));

        // Redirect based on referral type
        return match ($referralLink->referral_type->value) {
            'patient' => redirect('/')->with('referral_code', $referralCode),
            'agent' => redirect('/register')->with('referral_code', $referralCode),
            'business' => redirect('/')->with('referral_code', $referralCode),
            default => redirect('/'),
        };
    }
}

