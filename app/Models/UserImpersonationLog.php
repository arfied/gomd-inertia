<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserImpersonationLog extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';
    const STATUS_EXPIRED = 'expired';
    const STATUS_TERMINATED = 'terminated';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_ENDED => 'Ended',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_TERMINATED => 'Terminated',
    ];

    protected $fillable = [
        'impersonator_id',
        'impersonated_user_id',
        'security_code_hash',
        'started_at',
        'ended_at',
        'ip_address',
        'user_agent',
        'session_id',
        'status',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user who is performing the impersonation.
     */
    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    /**
     * Get the user who is being impersonated.
     */
    public function impersonatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonated_user_id');
    }

    /**
     * Check if the impersonation session is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE &&
               $this->ended_at === null;
    }

    /**
     * End the impersonation session.
     */
    public function endSession(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
            'reason' => $reason,
        ]);
    }

    /**
     * Get the duration of the impersonation session.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->ended_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->ended_at);
    }

    /**
     * Scope to get active impersonation sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->whereNull('ended_at');
    }

    /**
     * Scope to get sessions by impersonator.
     */
    public function scopeByImpersonator($query, $impersonatorId)
    {
        return $query->where('impersonator_id', $impersonatorId);
    }

    /**
     * Scope to get sessions for a specific user being impersonated.
     */
    public function scopeByImpersonatedUser($query, $userId)
    {
        return $query->where('impersonated_user_id', $userId);
    }
}
