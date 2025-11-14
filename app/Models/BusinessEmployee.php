<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessEmployee extends Model
{
    use HasFactory, SoftDeletes;

    const STATUSES = [
        'pending' => 'Pending',
        'active' => 'Active',
        'terminated' => 'Terminated',
    ];

    protected $fillable = [
        'business_id',
        'user_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'mobile_phone',
        'dob',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'status',
        'terminated_at',
        'termination_reason',
        'transitioned_to_consumer',
    ];

    protected $casts = [
        'dob' => 'date',
        'terminated_at' => 'datetime',
        'transitioned_to_consumer' => 'boolean',
    ];

    /**
     * Get the business that owns the employee.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the consultations for this employee.
     */
    public function consultations()
    {
        return $this->user ? $this->user->patientConsultations() : null;
    }

    /**
     * Get the full name of the employee.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check if the employee is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the employee is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the employee is terminated.
     */
    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    /**
     * Activate the employee.
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Terminate the employee.
     */
    public function terminate(string $reason = null): void
    {
        $this->update([
            'status' => 'terminated',
            'terminated_at' => Carbon::now(),
            'termination_reason' => $reason,
        ]);

        // Trigger webhook for consumer plan transition if needed
        // This would be implemented in a service provider or event listener
    }

    /**
     * Get the self-payments made by this employee.
     */
    public function selfPayments(): HasMany
    {
        return $this->hasMany(BusinessPlanSelfPayment::class);
    }

    /**
     * Get the completed self-payments made by this employee.
     */
    public function completedSelfPayments(): HasMany
    {
        return $this->hasMany(BusinessPlanSelfPayment::class)
            ->where('status', BusinessPlanSelfPayment::STATUS_COMPLETED);
    }

    /**
     * Check if the employee has made a self-payment for a specific business plan.
     */
    public function hasSelfPaidForPlan(BusinessPlan $plan): bool
    {
        return $this->selfPayments()
            ->where('business_plan_id', $plan->id)
            ->whereIn('status', [
                BusinessPlanSelfPayment::STATUS_PENDING,
                BusinessPlanSelfPayment::STATUS_COMPLETED
            ])
            ->exists();
    }

    /**
     * Check if the employee can make a self-payment.
     */
    public function canMakeSelfPayment(): bool
    {
        // Employee must be active
        if (!$this->isActive()) {
            return false;
        }

        // Employee must have a user account
        if (!$this->user_id || !$this->user) {
            return false;
        }

        return true;
    }
}
