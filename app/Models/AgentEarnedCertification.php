<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentEarnedCertification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'certification_id',
        'earned_at',
        'expires_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'earned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the agent that earned the certification.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the certification that was earned.
     */
    public function certification()
    {
        return $this->belongsTo(AgentCertification::class, 'certification_id');
    }

    /**
     * Determine if the certification is active.
     */
    public function isActive()
    {
        return $this->status === 'active' &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Determine if the certification is expired.
     */
    public function isExpired()
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
