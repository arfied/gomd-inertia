<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAnnouncement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'type',
        'target_tiers',
        'publish_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_tiers' => 'array',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the reads for this announcement.
     */
    public function reads()
    {
        return $this->hasMany(AgentAnnouncementRead::class, 'announcement_id');
    }

    /**
     * Get the agents who have read this announcement.
     */
    public function readByAgents()
    {
        return $this->belongsToMany(
            Agent::class,
            'agent_announcement_reads',
            'announcement_id',
            'agent_id'
        )->withPivot('read_at');
    }

    /**
     * Determine if the announcement is active.
     */
    public function isActive()
    {
        $now = now();

        return $now->gte($this->publish_at) &&
               ($this->expires_at === null || $now->lte($this->expires_at));
    }

    /**
     * Determine if the announcement is for a specific tier.
     */
    public function isForTier($tier)
    {
        return $this->target_tiers === null || in_array($tier, $this->target_tiers);
    }

    /**
     * Scope a query to only include active announcements.
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('publish_at', '<=', $now)
                     ->where(function ($query) use ($now) {
                         $query->whereNull('expires_at')
                               ->orWhere('expires_at', '>=', $now);
                     });
    }

    /**
     * Scope a query to only include announcements for a specific tier.
     */
    public function scopeForTier($query, $tier)
    {
        return $query->where(function ($query) use ($tier) {
            $query->whereNull('target_tiers')
                  ->orWhereRaw('JSON_CONTAINS(target_tiers, ?)', ['"' . $tier . '"']);
        });
    }
}
