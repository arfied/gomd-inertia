<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ReferralClick Model
 *
 * Tracks individual clicks on referral links.
 */
class ReferralClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_link_id',
        'ip_address',
        'user_agent',
        'referrer_url',
        'session_id',
        'converted',
        'converted_at',
    ];

    protected $casts = [
        'converted' => 'boolean',
        'converted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the referral link that was clicked.
     */
    public function referralLink(): BelongsTo
    {
        return $this->belongsTo(ReferralLink::class);
    }

    /**
     * Mark this click as converted.
     */
    public function markAsConverted(): void
    {
        $this->update([
            'converted' => true,
            'converted_at' => now(),
        ]);
    }

    /**
     * Scope to filter by referral link.
     */
    public function scopeForReferralLink($query, int $referralLinkId)
    {
        return $query->where('referral_link_id', $referralLinkId);
    }

    /**
     * Scope to filter converted clicks.
     */
    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    /**
     * Scope to filter unconverted clicks.
     */
    public function scopeUnconverted($query)
    {
        return $query->where('converted', false);
    }
};

