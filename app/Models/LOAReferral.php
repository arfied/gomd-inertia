<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LOAReferral extends Model
{
    /**
     * Valid referral types for LOA users
     * LOA users are the lowest in hierarchy and cannot refer agents
     */
    const REFERRAL_TYPES = [
        'patient' => 'Patient',
        'business' => 'Business'
    ];

    /**
     * Valid referral statuses
     */
    const STATUSES = [
        'pending' => 'Pending',
        'contacted' => 'Contacted',
        'converted' => 'Converted',
        'declined' => 'Declined'
    ];

    protected $table = 'loa_referrals';

    protected $fillable = [
        'loa_user_id',
        'target_agent_id',
        'referral_type',
        'referral_email',
        'referral_name',
        'referral_phone',
        'notes',
        'status',
        'tracking_code',
        'referral_url',
        'follow_up_date',
        'contacted_at',
        'converted_at',
        'conversion_data'
    ];

    protected $casts = [
        'follow_up_date' => 'datetime',
        'contacted_at' => 'datetime',
        'converted_at' => 'datetime',
        'conversion_data' => 'array'
    ];

    /**
     * Get the LOA user who created this referral.
     */
    public function loaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loa_user_id');
    }

    /**
     * Get the target agent for this referral.
     */
    public function targetAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'target_agent_id');
    }

    /**
     * Mark referral as contacted.
     */
    public function markAsContacted(): void
    {
        $this->update([
            'status' => 'contacted',
            'contacted_at' => now()
        ]);
    }

    /**
     * Mark referral as converted.
     */
    public function markAsConverted(array $conversionData = []): void
    {
        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
            'conversion_data' => $conversionData
        ]);
    }

    /**
     * Mark referral as declined.
     */
    public function markAsDeclined(): void
    {
        $this->update([
            'status' => 'declined'
        ]);
    }

    /**
     * Check if referral is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if referral is contacted.
     */
    public function isContacted(): bool
    {
        return $this->status === 'contacted';
    }

    /**
     * Check if referral is converted.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    /**
     * Check if referral is declined.
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Get the referral type label.
     */
    public function getReferralTypeLabel(): string
    {
        return self::REFERRAL_TYPES[$this->referral_type] ?? $this->referral_type;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Scope to filter by referral type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('referral_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by LOA user.
     */
    public function scopeForLOAUser($query, int $loaUserId)
    {
        return $query->where('loa_user_id', $loaUserId);
    }

    /**
     * Scope to filter by target agent.
     */
    public function scopeForTargetAgent($query, int $agentId)
    {
        return $query->where('target_agent_id', $agentId);
    }

    /**
     * Scope for recent referrals.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for pending referrals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for converted referrals.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }
}
