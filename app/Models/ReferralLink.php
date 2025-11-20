<?php

namespace App\Models;

use App\Enums\ReferralType;
use App\Enums\ReferralLinkStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ReferralLink Model
 *
 * Tracks referral links created by agents for different referral types.
 */
class ReferralLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'referral_type',
        'referral_code',
        'referral_token',
        'clicks_count',
        'conversions_count',
        'conversion_rate',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'referral_type' => ReferralType::class,
        'status' => ReferralLinkStatus::class,
        'clicks_count' => 'integer',
        'conversions_count' => 'integer',
        'conversion_rate' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the agent that owns this referral link.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Increment click count for this referral link.
     */
    public function recordClick(): void
    {
        $this->increment('clicks_count');
        $this->updateConversionRate();
    }

    /**
     * Increment conversion count for this referral link.
     */
    public function recordConversion(): void
    {
        $this->increment('conversions_count');
        $this->updateConversionRate();
    }

    /**
     * Update conversion rate based on clicks and conversions.
     */
    public function updateConversionRate(): void
    {
        if ($this->clicks_count > 0) {
            $this->conversion_rate = ($this->conversions_count / $this->clicks_count) * 100;
            $this->save();
        }
    }

    /**
     * Scope to filter by agent.
     */
    public function scopeForAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope to filter by referral type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('referral_type', $type);
    }

    /**
     * Scope to filter active links.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

