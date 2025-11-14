<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationOrderItem extends Model
{
    use HasFactory;

    // Define statuses as class constants for consistency across the application
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    // Add new statuses here in the future

    // Status labels for display
    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        // Add new status labels here in the future
    ];

    protected $fillable = [
        'medication_order_id',
        'medication_id',
        'custom_medication_name',
        'custom_medication_details',
        'requested_dosage',
        'requested_quantity',
        'status',
        'rejection_reason',
    ];

    /**
     * Get the medication order that owns the item.
     */
    public function medicationOrder()
    {
        return $this->belongsTo(MedicationOrder::class);
    }

    /**
     * Get the medication associated with the item.
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Check if this is a custom medication request.
     */
    public function isCustomMedication()
    {
        return $this->medication_id === null && $this->custom_medication_name !== null;
    }

    /**
     * Approve the medication order item.
     */
    public function approve()
    {
        $this->status = self::STATUS_APPROVED;
        $this->save();
    }

    /**
     * Reject the medication order item.
     */
    public function reject($reason = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        $this->save();
    }
}
