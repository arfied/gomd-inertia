<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'owner_fname',
        'owner_lname',
        'admin_user_id',
        'agent_id',
        'status',
        'trial_enabled',
        'trial_started_at',
        'trial_ends_at',
        'referring_agent_id',
        'loa_user_id',
        'enrollment_source'
    ];

    protected $casts = [
        'trial_enabled' => 'boolean',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the employees for the business.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(BusinessEmployee::class);
    }

    /**
     * Get the plans for the business.
     */
    public function plans(): HasMany
    {
        return $this->hasMany(BusinessPlan::class);
    }

    /**
     * Get the contacts for the business.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(BusinessContact::class);
    }

    /**
     * Get the primary contact for the business.
     */
    public function contact(): HasOne
    {
        return $this->hasOne(BusinessContact::class);
    }

    /**
     * Get the users associated with the business.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the business has an active trial.
     */
    public function hasActiveTrial(): bool
    {
        return $this->trial_enabled && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Get the active employees for the business.
     */
    public function activeEmployees()
    {
        return $this->employees()->where('status', 'active')->whereNotNull('user_id');
    }

    /**
     * Get the number of days remaining in the trial.
     */
    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->trial_enabled || !$this->trial_ends_at) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Check if the trial is active.
     */
    public function getIsTrialActiveAttribute(): bool
    {
        return $this->trial_enabled &&
               $this->trial_started_at &&
               $this->trial_ends_at &&
               Carbon::now()->lt($this->trial_ends_at);
    }

    /**
     * Get the total number of seats available based on plans.
     */
    public function getTotalSeatsAttribute(): int
    {
        return $this->plans()
            ->where('active', true)
            ->sum('plan_quantity');
    }

    /**
     * Get the number of available seats.
     */
    public function getAvailableSeatsAttribute(): int
    {
        return $this->total_seats - $this->employees()->where('status', 'active')->count();
    }

    /**
     * Get the agent who referred this business.
     */
    public function referringAgent()
    {
        return $this->belongsTo(Agent::class, 'referring_agent_id');
    }

    /**
     * Get the agent associated with this business.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    /**
     * Get the admin user for this business.
     */
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Get the LOA user who enrolled this business.
     */
    public function loaUser()
    {
        return $this->belongsTo(User::class, 'loa_user_id');
    }
}
