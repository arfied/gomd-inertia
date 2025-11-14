<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'plan_quantity',
        'price_per_plan',
        'total_price',
        'starts_at',
        'ends_at',
        'active',
        'duration_months',
        'discount_percent',
        'click_id',
        'afid',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price_per_plan' => 'integer',
        'total_price' => 'integer',
        'plan_quantity' => 'integer',
        'active' => 'boolean',
        'duration_months' => 'integer',
        'discount_percent' => 'integer',
        'click_id' => 'string',
        'afid' => 'string',
    ];

    /**
     * Get the business that owns the plan.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the self-payments for this business plan.
     */
    public function selfPayments(): HasMany
    {
        return $this->hasMany(BusinessPlanSelfPayment::class);
    }

    /**
     * Get the completed self-payments for this business plan.
     */
    public function completedSelfPayments(): HasMany
    {
        return $this->hasMany(BusinessPlanSelfPayment::class)
            ->where('status', BusinessPlanSelfPayment::STATUS_COMPLETED);
    }

    /**
     * Get the audit logs for this business plan.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(BusinessPlanAuditLog::class);
    }

    /**
     * Check if the plan is active.
     */
    public function isActive(): bool
    {
        return $this->active &&
               $this->starts_at <= now() &&
               ($this->ends_at === null || $this->ends_at >= now());
    }

    /**
     * Get the total price in dollars.
     */
    public function getTotalPriceInDollarsAttribute(): float
    {
        return $this->total_price / 100;
    }

    /**
     * Get the price per plan in dollars.
     */
    public function getPricePerPlanInDollarsAttribute(): float
    {
        return $this->price_per_plan / 100;
    }

    /**
     * Get price_per_plan in dollars (alternative accessor)
     */
    public function getPricePerPlanDollarsAttribute(): float
    {
        return $this->price_per_plan / 100;
    }

    /**
     * Set price_per_plan in dollars
     */
    public function setPricePerPlanDollarsAttribute(float $value): void
    {
        $this->attributes['price_per_plan'] = (int)($value * 100);
    }

    /**
     * Get total_price in dollars (alternative accessor)
     */
    public function getTotalPriceDollarsAttribute(): float
    {
        return $this->total_price / 100;
    }

    /**
     * Set total_price in dollars
     */
    public function setTotalPriceDollarsAttribute(float $value): void
    {
        $this->attributes['total_price'] = (int)($value * 100);
    }

    /**
     * Calculate the total price based on plan quantity
     */
    public function calculateTotalPrice(): int
    {
        // Calculate blocks - if quantity is 6-10, charge for 10 (2 blocks)
        $blocks = ceil($this->plan_quantity / 5);
        // Each block is charged at 5 × price_per_plan
        return $blocks * 5 * $this->price_per_plan;
    }

    /**
     * Calculate total price in dollars
     */
    public function calculateTotalPriceDollars(): float
    {
        return $this->calculateTotalPrice() / 100;
    }

    /**
     * Check if the plan has available quantity for self-payment.
     */
    public function hasAvailableQuantity(): bool
    {
        return $this->plan_quantity > 0;
    }

    /**
     * Get the number of completed self-payments.
     */
    public function getCompletedSelfPaymentsCountAttribute(): int
    {
        return $this->completedSelfPayments()->count();
    }

    /**
     * Get the remaining quantity after self-payments.
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->plan_quantity - $this->completed_self_payments_count);
    }

    /**
     * Check if an employee can make a self-payment for this plan.
     */
    public function canEmployeeSelfPay(BusinessEmployee $employee): bool
    {
        // Check if plan is active
        if (!$this->isActive()) {
            return false;
        }

        // Check if plan has available quantity
        if (!$this->hasAvailableQuantity()) {
            return false;
        }

        // Check if employee belongs to the same business
        if ($employee->business_id !== $this->business_id) {
            return false;
        }

        // Check if employee is active
        if (!$employee->isActive()) {
            return false;
        }

        // Check if employee has already made a self-payment for this plan
        $existingSelfPayment = $this->selfPayments()
            ->where('business_employee_id', $employee->id)
            ->whereIn('status', [
                BusinessPlanSelfPayment::STATUS_PENDING,
                BusinessPlanSelfPayment::STATUS_COMPLETED
            ])
            ->exists();

        return !$existingSelfPayment;
    }

    /**
     * Get detailed eligibility information for an employee.
     */
    public function getEmployeeSelfPayEligibility(BusinessEmployee $employee): array
    {
        // Check if plan is active
        if (!$this->isActive()) {
            return [
                'eligible' => false,
                'reason' => 'plan_inactive',
                'message' => 'The business plan is not currently active or has expired.'
            ];
        }

        // Check if plan has available quantity
        if (!$this->hasAvailableQuantity()) {
            return [
                'eligible' => false,
                'reason' => 'no_quantity',
                'message' => 'The business plan has no available quantity remaining.'
            ];
        }

        // Check if employee belongs to the same business
        if ($employee->business_id !== $this->business_id) {
            return [
                'eligible' => false,
                'reason' => 'wrong_business',
                'message' => 'Employee does not belong to the business associated with this plan.'
            ];
        }

        // Check if employee is active
        if (!$employee->isActive()) {
            return [
                'eligible' => false,
                'reason' => 'employee_inactive',
                'message' => 'Employee account is not active.'
            ];
        }

        // Check if employee has already made a self-payment for this plan
        $existingSelfPayment = $this->selfPayments()
            ->where('business_employee_id', $employee->id)
            ->whereIn('status', [
                BusinessPlanSelfPayment::STATUS_PENDING,
                BusinessPlanSelfPayment::STATUS_COMPLETED
            ])
            ->first();

        if ($existingSelfPayment) {
            $statusLabel = $existingSelfPayment->status === BusinessPlanSelfPayment::STATUS_PENDING
                ? 'pending'
                : 'completed';

            return [
                'eligible' => false,
                'reason' => 'existing_payment',
                'message' => "You already have a {$statusLabel} self-payment for this plan.",
                'existing_payment' => [
                    'id' => $existingSelfPayment->id,
                    'status' => $existingSelfPayment->status,
                    'amount' => $existingSelfPayment->amount_in_dollars,
                    'created_at' => $existingSelfPayment->created_at->format('M j, Y g:i A'),
                    'paid_at' => $existingSelfPayment->paid_at?->format('M j, Y g:i A'),
                ]
            ];
        }

        return [
            'eligible' => true,
            'reason' => null,
            'message' => 'Employee is eligible for self-payment.'
        ];
    }

    /**
     * Decrease plan quantity and total price after successful self-payment.
     * The business plan end date remains unchanged - only the employee gets extended coverage.
     */
    public function decreaseAfterSelfPayment(): array
    {
        $oldQuantity = $this->plan_quantity;
        $oldTotalPrice = $this->total_price;

        $newQuantity = max(0, $oldQuantity - 1);
        $newTotalPrice = max(0, $oldTotalPrice - $this->price_per_plan);

        $this->update([
            'plan_quantity' => $newQuantity,
            'total_price' => $newTotalPrice,
        ]);

        return [
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'old_total_price' => $oldTotalPrice,
            'new_total_price' => $newTotalPrice,
        ];
    }
}
