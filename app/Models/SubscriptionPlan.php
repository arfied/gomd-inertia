<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_months',
        'service_limit',
        'status',
        'is_active',
        'is_featured',
        'show_free_trial',
        'display_order',
        'group_id'
    ];

    protected $casts = [
        'features' => 'array',
        'benefits' => 'array',
        'insurance_eligible' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_free_trial' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getProratedPrice($daysLeft)
    {
        $dailyRate = $this->price / ($this->duration_months * 30);
        return round($dailyRate * $daysLeft, 2);
    }

    public function isUnlimited()
    {
        return is_null($this->service_limit);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class);
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class);
    }

    public function getActiveDiscountAttribute()
    {
        // Get the highest priority active regular discount (excluding ExtraHelp)
        return $this->discounts()
            ->where('discounts.status', 'active')
            ->where('discounts.start_date', '<=', now())
            ->where('discounts.end_date', '>=', now())
            ->where(function($query) {
                $query->whereNull('discounts.promo_code')
                    ->orWhere('discounts.promo_code', '!=', 'extrahelp');
            })
            ->orderBy('discounts.priority', 'desc')
            ->orderBy('discounts.value', 'desc')
            ->first();
    }

    /**
     * Get the ExtraHelp discount if it's active in the session
     */
    public function getExtraHelpDiscountAttribute()
    {
        // Check if there's a stacked discount in the session
        if (session()->has('stack_discount') && session()->has('extrahelp_discount_id')) {
            $discountId = session()->get('extrahelp_discount_id');
            $extraHelpDiscount = Discount::find($discountId);

            if ($extraHelpDiscount &&
                $extraHelpDiscount->status === 'active' &&
                $extraHelpDiscount->start_date <= now() &&
                $extraHelpDiscount->end_date >= now()) {
                return $extraHelpDiscount;
            }
        }

        return null;
    }

    /**
     * Calculate the total discount amount from all applicable discounts
     */
    public function getTotalDiscountAmountAttribute()
    {
        $totalDiscount = 0;
        $price = $this->price;

        // Apply regular discount first
        if ($this->activeDiscount) {
            if ($this->activeDiscount->type === 'percentage') {
                $totalDiscount += $price * ($this->activeDiscount->value / 100);
            } else {
                $totalDiscount += $this->activeDiscount->value;
            }
        }

        // Then apply ExtraHelp discount if available
        if ($this->extraHelpDiscount) {
            if ($this->extraHelpDiscount->type === 'percentage') {
                // Apply percentage to the already discounted price
                $discountedPrice = $price - $totalDiscount;
                $totalDiscount += $discountedPrice * ($this->extraHelpDiscount->value / 100);
            } else {
                // Add fixed amount
                $totalDiscount += $this->extraHelpDiscount->value;
            }
        }

        // Ensure discount doesn't exceed the price
        return min($totalDiscount, $price);
    }

    /**
     * Get the final price after all discounts
     */
    public function getFinalPriceAttribute()
    {
        return $this->price - $this->totalDiscountAmount;
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order plans by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('price');
    }

    /**
     * Scope a query to get featured plans first.
     */
    public function scopeFeatured($query)
    {
        return $query->orderBy('is_featured', 'desc');
    }

    /**
     * Get the group name for this subscription plan.
     */
    public function getGroupNameAttribute()
    {
        if (!$this->group_id) {
            return null;
        }

        $groups = [
            1 => 'Original Plans',
            2 => 'Trial Plans',
            3 => 'Premium Plans',
            4 => 'Business Plans'
        ];

        return $groups[$this->group_id] ?? null;
    }

    /**
     * Scope a query to only include plans from a specific group.
     */
    public function scopeInGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    /**
     * Scope a query to only include premium plans.
     */
    public function scopePremium($query)
    {
        return $query->where('group_id', 3); // Premium Plans
    }

    /**
     * Check if this plan is a premium plan.
     */
    public function isPremium()
    {
        return $this->group_id === 3;
    }

    /**
     * Check if this plan is a family plan.
     *
     * @return bool
     */
    public function isFamilyPlan(): bool
    {
        return $this->type === 'family' || $this->group_id === 4; // Business Plans group also supports family functionality
    }

    /**
     * Get the maximum number of dependents allowed for this plan.
     *
     * @return int
     */
    public function getMaxDependentsAttribute(): int
    {
        return $this->isFamilyPlan() ? 6 : 0;
    }

    /**
     * Get the commission frequency based on the plan's duration.
     *
     * @return string
     */
    public function getCommissionFrequency(): string
    {
        // Map duration_months to commission frequency
        switch ($this->duration_months) {
            case 1:
                return 'monthly';
            case 6:
                return 'biannual';
            case 12:
                return 'annual';
            default:
                // For any other duration, default to monthly
                // This handles cases like 3-month plans or custom durations
                return 'monthly';
        }
    }

    /**
     * Check if this plan uses biannual commission rates.
     *
     * @return bool
     */
    public function isBiannualCommission(): bool
    {
        return $this->getCommissionFrequency() === 'biannual';
    }

    /**
     * Check if this plan uses annual commission rates.
     *
     * @return bool
     */
    public function isAnnualCommission(): bool
    {
        return $this->getCommissionFrequency() === 'annual';
    }

    /**
     * Get the maximum number of younger dependents (23 or younger) allowed for this plan.
     *
     * @return int
     */
    public function getMaxYoungerDependentsAttribute(): int
    {
        return $this->isFamilyPlan() ? 4 : 0;
    }



    /**
     * Get the maximum number of adult dependents allowed for this plan.
     *
     * Note: There is no specific limit on adult dependents (18-23) as long as
     * the total number of younger dependents (≤23) doesn't exceed 4.
     *
     * @return int
     * @deprecated Use getMaxYoungerDependentsAttribute() instead
     */
    public function getMaxAdultDependentsAttribute(): int
    {
        // No specific limit on adult dependents, only on total younger dependents
        return $this->getMaxYoungerDependentsAttribute();
    }

    /**
     * Get the maximum number of older dependents (24+) allowed for this plan.
     *
     * @return int
     */
    public function getMaxOlderDependentsAttribute(): int
    {
        return $this->isFamilyPlan() ? 1 : 0;
    }
}
