<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    use HasFactory;

    // Define status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PENDING_PAYMENT = 'pending_payment';

    // Family role constants
    const FAMILY_ROLE_PRIMARY = 'primary';
    const FAMILY_ROLE_DEPENDENT_ADULT = 'dependent_adult';
    const FAMILY_ROLE_DEPENDENT_MINOR = 'dependent_minor';
    const FAMILY_ROLE_DEPENDENT_OLDER = 'dependent_older';

    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'status',
        'afid',
        'click_id',
        'is_discounted',
        'discounted_price',
        'cancelled_at',
        'is_trial',
        'meta_data',
        'agent_id',
        'user_type',
        'loa_user_id',
        'enrollment_source',
        'is_primary_account',
        'primary_subscription_id',
        'family_role',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_trial' => 'boolean',
        'is_primary_account' => 'boolean',
        'meta_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * $subscription = Subscription::find(1);
     *
     *  // Get a value
     *  $maxConsultations = $subscription->getFeature('usage_limits.max_consultations', 5);
     *
     *  // Set a value
     *  $subscription->setFeature('billing_info.payment_method', 'credit_card');
     *
     *  // Check if a feature exists
     *  if ($subscription->hasFeature('promotional.referral_code')) {
     *      // Do something
     *  }
     */
    public function getFeature($key, $default = null)
    {
        return data_get($this->meta_data, $key, $default);
    }

    public function setFeature($key, $value)
    {
        $metaData = $this->meta_data;
        data_set($metaData, $key, $value);
        $this->meta_data = $metaData;
        $this->save();
    }

    public function hasFeature($key)
    {
        return data_get($this->meta_data, $key) !== null;
    }

    /**
     * When creating or updating a subscription with no limit, you would set the field to NULL:
     *  $subscription->prescription_limit = null; // unlimited prescriptions
     *  $subscription->medication_coverage_limit = null; // unlimited coverage
     *
     * In your database queries, you can easily filter for unlimited subscriptions:
     *  $unlimitedPrescriptionPlans = Subscription::whereNull('prescription_limit')->get();
     */
    public function hasUnlimitedPrescriptions()
    {
        return $this->prescription_limit === null;
    }

    public function hasUnlimitedMedicationCoverage()
    {
        return $this->medication_coverage_limit === null;
    }

    public function getRemainingPrescriptions(): int
    {
        $this->checkAndResetUsage();

        if ($this->hasUnlimitedPrescriptions()) {
            return PHP_INT_MAX; // effectively unlimited
        }
        return max(0, $this->prescription_limit - $this->used_prescription_count);
    }

    public function getRemainingMedicationCoverage(): float
    {
        $this->checkAndResetUsage();

        if ($this->hasUnlimitedMedicationCoverage()) {
            return PHP_FLOAT_MAX; // effectively unlimited
        }
        return max(0, $this->medication_coverage_limit - $this->used_medication_coverage);
    }

    protected function checkAndResetUsage(): void
    {
        $now = Carbon::now();
        if ($this->coverage_reset_date === null) {
            $this->coverage_reset_date = $this->starts_at->addMonth();
            $this->save();
        }

        if ($now->greaterThanOrEqualTo($this->coverage_reset_date)) {
            $this->used_prescription_count = 0;
            $this->used_medication_coverage = 0;
            $this->coverage_reset_date = $now->addMonth();
            $this->save();
        }
    }

    public function incrementUsage(int $prescriptionCount, float $coverageAmount): void
    {
        $this->checkAndResetUsage();
        $this->used_prescription_count += $prescriptionCount;
        $this->used_medication_coverage += $coverageAmount;
        $this->save();
    }

    /**
     * Check if the subscription is pending payment.
     *
     * @return bool
     */
    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    /**
     * Mark the subscription as pending payment.
     *
     * @return void
     */
    public function markAsPendingPayment(): void
    {
        $this->status = self::STATUS_PENDING_PAYMENT;
        $this->save();
    }

    /**
     * Activate the subscription after payment is complete.
     *
     * @return void
     */
    public function activateAfterPayment(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Get the latest transaction for the subscription.
     */
    public function latestTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    /**
     * Get all transactions for the subscription.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the agent that owns the subscription.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the LOA user who created this subscription.
     */
    public function loaUser()
    {
        return $this->belongsTo(User::class, 'loa_user_id');
    }

    /**
     * Get the primary subscription that this dependent subscription belongs to.
     */
    public function primarySubscription()
    {
        return $this->belongsTo(Subscription::class, 'primary_subscription_id');
    }

    /**
     * Get all dependent subscriptions for this primary subscription.
     */
    public function dependentSubscriptions()
    {
        return $this->hasMany(Subscription::class, 'primary_subscription_id');
    }

    /**
     * Get all family members associated with this subscription.
     */
    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Check if this subscription is a primary account.
     *
     * @return bool
     */
    public function isPrimaryAccount(): bool
    {
        return $this->is_primary_account;
    }

    /**
     * Check if this subscription is a dependent account.
     *
     * @return bool
     */
    public function isDependentAccount(): bool
    {
        return !$this->is_primary_account && $this->primary_subscription_id !== null;
    }

    /**
     * Check if this subscription is for a minor dependent.
     *
     * @return bool
     */
    public function isMinorDependent(): bool
    {
        return $this->family_role === self::FAMILY_ROLE_DEPENDENT_MINOR;
    }

    /**
     * Check if this subscription is for an adult dependent (18-23).
     *
     * @return bool
     */
    public function isAdultDependent(): bool
    {
        return $this->family_role === self::FAMILY_ROLE_DEPENDENT_ADULT;
    }

    /**
     * Check if this subscription is for an older dependent (24+).
     *
     * @return bool
     */
    public function isOlderDependent(): bool
    {
        return $this->family_role === self::FAMILY_ROLE_DEPENDENT_OLDER;
    }

    /**
     * Get the count of dependents for this primary subscription.
     *
     * @return int
     */
    public function getDependentCount(): int
    {
        if (!$this->isPrimaryAccount()) {
            return 0;
        }

        return $this->dependentSubscriptions()->count();
    }

    /**
     * Get the count of younger dependents (23 or younger) for this primary subscription.
     *
     * @return int
     */
    public function getYoungerDependentCount(): int
    {
        if (!$this->isPrimaryAccount()) {
            return 0;
        }

        return $this->dependentSubscriptions()
            ->where(function($query) {
                $query->where('family_role', self::FAMILY_ROLE_DEPENDENT_MINOR)
                      ->orWhere('family_role', self::FAMILY_ROLE_DEPENDENT_ADULT);
            })
            ->count();
    }

    /**
     * Get the count of adult dependents for this primary subscription.
     *
     * @return int
     */
    public function getAdultDependentCount(): int
    {
        if (!$this->isPrimaryAccount()) {
            return 0;
        }

        return $this->dependentSubscriptions()
            ->where('family_role', 'dependent_adult')
            ->count();
    }

    /**
     * Get the count of older dependents (24+) for this primary subscription.
     *
     * @return int
     */
    public function getOlderDependentCount(): int
    {
        if (!$this->isPrimaryAccount()) {
            return 0;
        }

        // Count dependents with family_role = 'dependent_older'
        return $this->dependentSubscriptions()
            ->where('family_role', self::FAMILY_ROLE_DEPENDENT_OLDER)
            ->count();
    }

    /**
     * Check if this primary subscription has reached the maximum number of dependents.
     *
     * @return bool
     */
    public function hasReachedMaxDependents(): bool
    {
        if (!$this->isPrimaryAccount()) {
            return false;
        }

        // Check for admin override
        $maxDependents = $this->getFeature('admin_overrides.max_total_dependents', 5);

        // Maximum dependents per family plan (default: 5)
        return $this->getDependentCount() >= $maxDependents;
    }

    /**
     * Check if this primary subscription has reached the maximum number of adult dependents.
     *
     * Note: There is no specific limit on adult dependents (18-23) as long as
     * the total number of younger dependents (≤23) doesn't exceed 4.
     *
     * @return bool
     * @deprecated Use hasReachedMaxYoungerDependents() instead
     */
    public function hasReachedMaxAdultDependents(): bool
    {
        // No specific limit on adult dependents, only on total younger dependents
        return $this->hasReachedMaxYoungerDependents();
    }

    /**
     * Check if this primary subscription has reached the maximum number of older dependents (24+).
     *
     * @return bool
     */
    public function hasReachedMaxOlderDependents(): bool
    {
        if (!$this->isPrimaryAccount()) {
            return false;
        }

        // Check for admin override
        $maxOlderDependents = $this->getFeature('admin_overrides.max_older_dependents', 1);

        // Maximum of older dependents (24+) per family plan (default: 1)
        return $this->getOlderDependentCount() >= $maxOlderDependents;
    }

    /**
     * Check if this primary subscription has reached the maximum number of younger dependents (23 or younger).
     *
     * @return bool
     */
    public function hasReachedMaxYoungerDependents(): bool
    {
        if (!$this->isPrimaryAccount()) {
            return false;
        }

        // Check for admin override
        $maxYoungerDependents = $this->getFeature('admin_overrides.max_younger_dependents', 4);

        // Maximum of younger dependents (23 or younger) per family plan (default: 4)
        return $this->getYoungerDependentCount() >= $maxYoungerDependents;
    }
}
