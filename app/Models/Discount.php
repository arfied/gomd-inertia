<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_UPCOMING = 'upcoming';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'name',
        'promo_code',
        'description',
        'type',
        'value',
        'start_date',
        'end_date',
        'status',
        'priority',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'value' => 'float',
    ];

    /**
     * The subscription plans that belong to the discount.
     */
    public function subscriptionPlans()
    {
        return $this->belongsToMany(SubscriptionPlan::class);
    }

    public function activate() {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate() {
        $this->is_active = false;
        $this->save();
    }

    /**
     * Get the discount amount for a given price.
     *
     * @param float $price
     * @return float
     */
    public function getDiscountedAmount(float $price): float
    {
        if ($this->type === 'percentage') {
            return $price * ($this->value / 100);
        } elseif ($this->type === 'fixed') {
            return max($price - $this->value, 0);
        }
        return 0;  // Return 0 if discount type is invalid
    }

    /**
     * Get the discount percentage for a given price.
     *
     * @param float $price
     * @return float
     */
    public function getDiscountPercentage(float $price): float
    {
        if ($this->type === 'percentage') {
            return $this->value;
        } elseif ($this->type === 'fixed') {
            return ($this->value / $price) * 100;
        }
        return 0;  // Return 0 if discount type is invalid
    }

    /**
     * Check if the discount applies to a specific plan.
     *
     * @param int $planId
     * @return bool
     */
    public function appliesToPlan(int $planId): bool
    {
        return $this->subscriptionPlans()->where('subscription_plan_id', $planId)->exists();
    }

    /**
     * Apply the discount to a given price.
     *
     * @param float $price
     * @return float
     */
    public function applyDiscount(float $price): float
    {
        if ($this->type === 'percentage') {
            return $price - ($price * ($this->value / 100));
        } elseif ($this->type === 'fixed') {
            return max($price - $this->value, 0);  // Ensure the price doesn't go below 0
        }
        return $price;  // Return original price if discount type is invalid
    }

    /**
     * Check if the discount is currently active.
     *
     * @return bool
     */
    public function is_active(): bool
    {
        $now = now();
        return $this->status === self::STATUS_ACTIVE
            && $this->start_date <= $now
            && $this->end_date >= $now;
    }

    /**
     * Update the status based on current date.
     *
     * @return void
     */
    public function updateStatus(): void
    {
        $now = now();
        if ($this->status === self::STATUS_UPCOMING && $this->start_date <= $now) {
            $this->status = self::STATUS_ACTIVE;
        } elseif ($this->status === self::STATUS_ACTIVE && $this->end_date < $now) {
            $this->status = self::STATUS_EXPIRED;
        }
        $this->save();
    }

    /**
     * Scope a query to only include active discounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }
}
