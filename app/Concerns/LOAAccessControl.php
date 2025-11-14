<?php

namespace App\Concerns;

trait LOAAccessControl
{
    /**
     * Check if the user has LOA access permissions.
     *
     * @return bool
     */
    public function hasLOAAccess(): bool
    {
        return $this->hasRole('loa');
    }

    /**
     * Check if the user can access commission-related features.
     *
     * @return bool
     */
    public function canAccessCommissions(): bool
    {
        // LOA users cannot access commission features
        if ($this->hasRole('loa')) {
            return false;
        }

        // Must be an agent with approved status
        return $this->hasRole('agent') && $this->agent && $this->agent->status === 'approved';
    }

    /**
     * Check if the user can access agent dashboard features.
     *
     * @return bool
     */
    public function canAccessAgentDashboard(): bool
    {
        return $this->hasRole('agent') && $this->agent && $this->agent->status === 'approved';
    }

    /**
     * Check if the user can access LOA dashboard features.
     *
     * @return bool
     */
    public function canAccessLOADashboard(): bool
    {
        return $this->hasRole('loa');
    }

    /**
     * Check if the user can create referrals.
     *
     * @return bool
     */
    public function canCreateReferrals(): bool
    {
        return $this->hasRole('agent') || $this->hasRole('loa');
    }

    /**
     * Check if LOA user has a managing agent.
     *
     * @return bool
     */
    public function hasManagingAgent(): bool
    {
        return $this->hasRole('loa') && !empty($this->managing_agent_id);
    }

    /**
     * Get the managing agent for this LOA user.
     *
     * @return \App\Models\Agent|null
     */
    public function getManagingAgent(): ?\App\Models\Agent
    {
        if (!$this->hasManagingAgent()) {
            return null;
        }

        return $this->managingAgent;
    }

    /**
     * Check if the user can view referral analytics.
     *
     * @return bool
     */
    public function canViewReferralAnalytics(): bool
    {
        // LOA users can view basic referral stats but not commission data
        if ($this->hasRole('loa')) {
            return true;
        }

        // Agents can view full analytics
        return $this->canAccessAgentDashboard();
    }

    /**
     * Check if the user can manage tier settings.
     *
     * @return bool
     */
    public function canManageTiers(): bool
    {
        // Only agents can manage tiers, LOA users cannot
        return $this->hasRole('agent') && $this->agent && $this->agent->status === 'approved';
    }

    /**
     * Get the appropriate dashboard route for the user.
     *
     * @return string
     */
    public function getDashboardRoute(): string
    {
        if ($this->hasRole('loa')) {
            return 'loa.dashboard';
        }

        if ($this->hasRole('agent')) {
            return 'agent.dashboard';
        }

        // Default fallback
        return 'home';
    }

    /**
     * Get role-specific navigation items.
     *
     * @return array
     */
    public function getRoleNavigation(): array
    {
        if ($this->hasRole('loa')) {
            return [
                'dashboard' => [
                    'route' => 'loa.dashboard',
                    'label' => 'LOA Dashboard',
                    'icon' => 'dashboard'
                ],
                'referrals' => [
                    'route' => 'loa.referrals',
                    'label' => 'My Referrals',
                    'icon' => 'users'
                ],
                'analytics' => [
                    'route' => 'loa.analytics',
                    'label' => 'Referral Analytics',
                    'icon' => 'chart-bar'
                ],
                'profile' => [
                    'route' => 'loa.profile',
                    'label' => 'Profile',
                    'icon' => 'user'
                ]
            ];
        }

        if ($this->hasRole('agent')) {
            return [
                'dashboard' => [
                    'route' => 'agent.dashboard',
                    'label' => 'Agent Dashboard',
                    'icon' => 'dashboard'
                ],
                'referrals' => [
                    'route' => 'agent.referrals',
                    'label' => 'Referrals',
                    'icon' => 'users'
                ],
                'commissions' => [
                    'route' => 'agent.commissions',
                    'label' => 'Commissions',
                    'icon' => 'currency-dollar'
                ],
                'analytics' => [
                    'route' => 'agent.analytics',
                    'label' => 'Analytics',
                    'icon' => 'chart-bar'
                ],
                'profile' => [
                    'route' => 'agent.profile.edit',
                    'label' => 'Profile',
                    'icon' => 'user'
                ]
            ];
        }

        return [];
    }

    /**
     * Get accessible features based on role.
     *
     * @return array
     */
    public function getAccessibleFeatures(): array
    {
        $features = [
            'referrals' => $this->canCreateReferrals(),
            'analytics' => $this->canViewReferralAnalytics(),
            'commissions' => $this->canAccessCommissions(),
            'tier_management' => $this->canManageTiers(),
            'agent_dashboard' => $this->canAccessAgentDashboard(),
            'loa_dashboard' => $this->canAccessLOADashboard(),
        ];

        return array_filter($features);
    }
}
