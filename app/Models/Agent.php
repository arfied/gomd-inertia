<?php

namespace App\Models;

use App\Domain\Commission\CommissionCalculationEngine;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Agent extends Model
{
    use HasFactory;

    /**
     * Get commission rates for a specific payment frequency.
     *
     * Uses the CommissionCalculationEngine as the source of truth for commission rates.
     *
     * @param string $frequency Payment frequency (monthly, biannual, annual)
     * @return array Commission rates by tier (uppercase keys)
     */
    public static function getCommissionRates(string $frequency = 'monthly'): array
    {
        $rates = CommissionCalculationEngine::COMMISSION_RATES[$frequency] ?? CommissionCalculationEngine::COMMISSION_RATES['monthly'];

        // Convert to uppercase keys to match tier names
        $result = [];
        foreach ($rates as $tier => $rate) {
            $result[strtoupper($tier)] = $rate;
        }

        return $result;
    }

    // Tier hierarchy (higher index = higher tier)
    const TIER_HIERARCHY = [
        'ASSOCIATE' => 0,
        'AGENT' => 1,
        'MGA' => 2,
        'SVG' => 3,
        'FMO' => 4,
        'SFMO' => 5
    ];

    protected $fillable = [
        'user_id',
        'referring_agent_id',
        'company',
        'experience',
        'status',
        'tier',
        'commission_rate',
        'referral_code',
        'referral_token',
        'npn'
    ];

    /**
     * Get the user that owns the agent profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agent who referred this agent.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referring_agent_id');
    }

    /**
     * Get the agents referred by this agent.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Agent::class, 'referring_agent_id');
    }

    /**
     * Get the commissions earned by this agent.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AgentCommission::class);
    }

    /**
     * Get the upline commissions earned by this agent.
     */
    public function uplineCommissions(): HasMany
    {
        return $this->hasMany(AgentCommission::class, 'upline_agent_id');
    }

    /**
     * Get the patients directly referred by this agent.
     */
    public function referredPatients()
    {
        return $this->hasMany(User::class, 'referring_agent_id');
    }

    /**
     * Get the businesses directly referred by this agent.
     */
    public function referredBusinesses()
    {
        return $this->hasMany(Business::class, 'referring_agent_id');
    }

    /**
     * Generate a unique referral code and token for this agent.
     */
    public function generateReferralCode(): void
    {
        $this->referral_code = strtoupper(Str::random(8));
        $this->referral_token = Str::uuid();
        $this->save();
    }



    /**
     * Get the allowed tiers for new agents referred by this agent.
     */
    public function getAllowedTiersForReferrals(): array
    {
        $myTierLevel = self::TIER_HIERARCHY[$this->tier] ?? 0;
        $tiers = self::getCommissionRates('monthly');

        // Filter tiers that are lower than the current agent's tier
        return array_filter($tiers, function($tier, $tierName) use ($myTierLevel) {
            return self::TIER_HIERARCHY[$tierName] < $myTierLevel;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Generate tier-specific referral URLs for this agent.
     *
     * @return array Array of tier-specific referral URLs
     */
    public function generateTierSpecificReferralUrls(): array
    {
        // Ensure agent has a referral code
        if (!$this->referral_code) {
            $this->generateReferralCode();
        }

        $allowedTiers = $this->getAllowedTiersForReferrals();
        $referralUrls = [];

        foreach ($allowedTiers as $tierName => $rate) {
            $referralUrls[$tierName] = [
                'url' => url('/agent/register') . '?agent_ref=' . $this->referral_code . '&tier=' . $tierName,
                'rate' => $rate
            ];
        }

        return $referralUrls;
    }

    /**
     * Generate patient referral URLs for this agent.
     *
     * @return array Array of patient referral URLs
     */
    public function generatePatientReferralUrls(): array
    {
        // Ensure agent has a referral code
        if (!$this->referral_code) {
            $this->generateReferralCode();
        }

        return [
            'rx' => [
                'name' => 'Prescription Medication',
                'url' => url('/rx') . '?agent_ref=' . $this->referral_code,
            ],
            'urgent_care' => [
                'name' => 'Urgent Care',
                'url' => url('/urgent-care') . '?agent_ref=' . $this->referral_code,
            ],
            'health_plan' => [
                'name' => 'Health Plan',
                'url' => url('/health-plan') . '?agent_ref=' . $this->referral_code,
            ],
            'home' => [
                'name' => 'General Registration',
                'url' => url('/') . '?agent_ref=' . $this->referral_code,
            ],
        ];
    }

    /**
     * Generate business referral URL for this agent.
     *
     * @return string Business referral URL
     */
    public function generateBusinessReferralUrl(): string
    {
        // Ensure agent has a referral code
        if (!$this->referral_code) {
            $this->generateReferralCode();
        }

        return url('/business/register') . '?agent_ref=' . $this->referral_code;
    }

    /**
     * Get the complete upline hierarchy for this agent.
     * Returns an array of agents from immediate referrer to top-level.
     */
    public function getUplineHierarchy(): array
    {
        $upline = [];
        $currentAgent = $this;

        while ($currentAgent->referring_agent_id) {
            $uplineAgent = $currentAgent->referrer;
            if (!$uplineAgent) {
                break;
            }

            $upline[] = $uplineAgent;
            $currentAgent = $uplineAgent;
        }

        return $upline;
    }

    /**
     * Get the complete downline hierarchy for this agent.
     * Returns a nested array structure with all levels of referrals.
     */
    public function getDownlineHierarchy(): array
    {
        $downline = [];

        // Get direct referrals with their user data
        $directReferrals = $this->referrals()->with('user')->get();

        foreach ($directReferrals as $referral) {
            $referralData = [
                'agent' => $referral,
                'level' => 1,
                'children' => $this->getDownlineHierarchyRecursive($referral, 2)
            ];
            $downline[] = $referralData;
        }

        return $downline;
    }

    /**
     * Recursive helper method for building downline hierarchy.
     */
    private function getDownlineHierarchyRecursive(Agent $agent, int $level): array
    {
        $children = [];
        $referrals = $agent->referrals()->with('user')->get();

        foreach ($referrals as $referral) {
            $children[] = [
                'agent' => $referral,
                'level' => $level,
                'children' => $this->getDownlineHierarchyRecursive($referral, $level + 1)
            ];
        }

        return $children;
    }

    /**
     * Get a flattened list of all downline agents with their hierarchy level.
     */
    public function getFlattenedDownline(): array
    {
        $flattened = [];
        $hierarchy = $this->getDownlineHierarchy();

        $this->flattenHierarchy($hierarchy, $flattened);

        return $flattened;
    }

    /**
     * Recursive helper to flatten the hierarchy structure.
     */
    private function flattenHierarchy(array $hierarchy, array &$flattened): void
    {
        foreach ($hierarchy as $item) {
            $flattened[] = [
                'agent' => $item['agent'],
                'level' => $item['level']
            ];

            if (!empty($item['children'])) {
                $this->flattenHierarchy($item['children'], $flattened);
            }
        }
    }

    /**
     * Get the training progress for this agent.
     */
    public function trainingProgress(): HasMany
    {
        return $this->hasMany(AgentTrainingProgress::class);
    }

    /**
     * Get the earned certifications for this agent.
     */
    public function earnedCertifications(): HasMany
    {
        return $this->hasMany(AgentEarnedCertification::class);
    }

    /**
     * Get the marketing usage records for this agent.
     */
    public function marketingUsage(): HasMany
    {
        return $this->hasMany(AgentMarketingUsage::class);
    }

    /**
     * Get the landing pages for this agent.
     */
    public function landingPages(): HasMany
    {
        return $this->hasMany(AgentLandingPage::class);
    }

    /**
     * Get the messages sent by this agent.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(AgentMessage::class, 'sender_id', 'user_id');
    }

    /**
     * Get the messages received by this agent.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(AgentMessage::class, 'recipient_id', 'user_id');
    }

    /**
     * Get the announcement reads for this agent.
     */
    public function announcementReads(): HasMany
    {
        return $this->hasMany(AgentAnnouncementRead::class);
    }

    /**
     * Get the support tickets for this agent.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(AgentSupportTicket::class);
    }

    /**
     * Get the goals for this agent.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(AgentGoal::class);
    }

    /**
     * Get the LOA users managed by this agent.
     */
    public function managedLOAs(): HasMany
    {
        return $this->hasMany(User::class, 'managing_agent_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'loa');
            });
    }

    /**
     * Get the payouts for this agent.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(AgentPayout::class);
    }

    /**
     * Get the LOA referrals created by this agent's managed LOAs.
     */
    public function loaReferrals(): HasMany
    {
        return $this->hasMany(LOAReferral::class, 'target_agent_id');
    }

    /**
     * Get all patients referred by this agent's LOAs.
     */
    public function loaReferredPatients()
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'patient');
            })
            ->whereIn('referring_agent_id', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('managing_agent_id', $this->id)
                    ->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'loa');
                    });
            });
    }

    /**
     * Check if this agent can manage LOA users.
     *
     * @return bool
     */
    public function canManageLOAs(): bool
    {
        // Only certain tiers can manage LOAs
        $allowedTiers = ['ASSOCIATE', 'AGENT', 'MGA', 'SVG', 'FMO', 'SFMO'];

        if (!in_array($this->tier, $allowedTiers)) {
            return false;
        }

        // Agent must be approved and active
        return $this->status === 'approved' && $this->user && $this->user->status === 'active';
    }

    /**
     * Get the custom reports for this agent.
     */
    public function customReports(): HasMany
    {
        return $this->hasMany(AgentCustomReport::class);
    }

    /**
     * Get the report exports for this agent.
     */
    public function reportExports(): HasMany
    {
        return $this->hasMany(AgentReportExport::class);
    }

    /**
     * Get the notification settings for this agent.
     */
    public function notificationSettings(): HasOne
    {
        return $this->hasOne(NotificationSetting::class);
    }

    /**
     * Get the subscriptions associated with this agent.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
