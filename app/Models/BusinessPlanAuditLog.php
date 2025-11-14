<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPlanAuditLog extends Model
{
    use HasFactory;

    const ACTION_SELF_PAYMENT_COMPLETED = 'self_payment_completed';
    const ACTION_QUANTITY_DECREASED = 'quantity_decreased';
    const ACTION_TOTAL_PRICE_DECREASED = 'total_price_decreased';
    const ACTION_PLAN_UPDATED = 'plan_updated';
    const ACTION_SELF_PAYMENT_FAILED = 'self_payment_failed';
    const ACTION_SELF_PAYMENT_REFUNDED = 'self_payment_refunded';

    const SOURCE_SYSTEM = 'system';
    const SOURCE_ADMIN = 'admin';
    const SOURCE_EMPLOYEE_SELF_PAYMENT = 'employee_self_payment';

    protected $fillable = [
        'business_plan_id',
        'user_id',
        'business_employee_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'source',
        'ip_address',
        'user_agent',
        'self_payment_id',
        'transaction_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the business plan that this audit log belongs to.
     */
    public function businessPlan(): BelongsTo
    {
        return $this->belongsTo(BusinessPlan::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business employee if this is related to an employee action.
     */
    public function businessEmployee(): BelongsTo
    {
        return $this->belongsTo(BusinessEmployee::class);
    }

    /**
     * Get the self-payment if this audit log is related to a self-payment.
     */
    public function selfPayment(): BelongsTo
    {
        return $this->belongsTo(BusinessPlanSelfPayment::class, 'self_payment_id');
    }

    /**
     * Get the transaction if this audit log is related to a transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Create an audit log entry for a self-payment completion.
     */
    public static function logSelfPaymentCompleted(
        BusinessPlan $businessPlan,
        BusinessEmployee $employee,
        BusinessPlanSelfPayment $selfPayment,
        array $oldValues,
        array $newValues,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::create([
            'business_plan_id' => $businessPlan->id,
            'user_id' => $employee->user_id,
            'business_employee_id' => $employee->id,
            'action' => self::ACTION_SELF_PAYMENT_COMPLETED,
            'description' => "Employee {$employee->full_name} completed self-payment of $20.00",
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'source' => self::SOURCE_EMPLOYEE_SELF_PAYMENT,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'self_payment_id' => $selfPayment->id,
            'transaction_id' => $selfPayment->transaction_id,
        ]);
    }

    /**
     * Create an audit log entry for plan quantity decrease.
     */
    public static function logQuantityDecrease(
        BusinessPlan $businessPlan,
        int $oldQuantity,
        int $newQuantity,
        BusinessPlanSelfPayment $selfPayment = null,
        string $source = self::SOURCE_SYSTEM
    ): self {
        return self::create([
            'business_plan_id' => $businessPlan->id,
            'user_id' => $selfPayment?->user_id,
            'business_employee_id' => $selfPayment?->business_employee_id,
            'action' => self::ACTION_QUANTITY_DECREASED,
            'description' => "Plan quantity decreased from {$oldQuantity} to {$newQuantity}",
            'old_values' => ['plan_quantity' => $oldQuantity],
            'new_values' => ['plan_quantity' => $newQuantity],
            'source' => $source,
            'self_payment_id' => $selfPayment?->id,
            'transaction_id' => $selfPayment?->transaction_id,
        ]);
    }

    /**
     * Create an audit log entry for total price decrease.
     */
    public static function logTotalPriceDecrease(
        BusinessPlan $businessPlan,
        int $oldTotalPrice,
        int $newTotalPrice,
        BusinessPlanSelfPayment $selfPayment = null,
        string $source = self::SOURCE_SYSTEM
    ): self {
        return self::create([
            'business_plan_id' => $businessPlan->id,
            'user_id' => $selfPayment?->user_id,
            'business_employee_id' => $selfPayment?->business_employee_id,
            'action' => self::ACTION_TOTAL_PRICE_DECREASED,
            'description' => "Total price decreased from $" . ($oldTotalPrice / 100) . " to $" . ($newTotalPrice / 100),
            'old_values' => ['total_price' => $oldTotalPrice],
            'new_values' => ['total_price' => $newTotalPrice],
            'source' => $source,
            'self_payment_id' => $selfPayment?->id,
            'transaction_id' => $selfPayment?->transaction_id,
        ]);
    }
}
