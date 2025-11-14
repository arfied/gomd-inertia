<?php

namespace App\Models;

use App\Notifications\MedicationOrderAssigned;
use App\Notifications\MedicationOrderCreated;
use App\Notifications\MedicationOrderStatusChanged;
use App\Concerns\FormatsDateAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class MedicationOrder extends Model
{
    use HasFactory, FormatsDateAttributes;

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($medicationOrder) {
            // Notify admins about the new order
            $admins = User::role('admin')->get();
            Notification::send($admins, new MedicationOrderCreated($medicationOrder));
        });
    }

    // Define statuses as class constants for consistency across the application
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PRESCRIBED = 'prescribed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    // Add new statuses here in the future

    // Status labels for display
    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ASSIGNED => 'Assigned to Doctor',
        self::STATUS_PRESCRIBED => 'Prescribed',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_REJECTED => 'Rejected',
        // Add new status labels here in the future
    ];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'prescription_id',
        'status',
        'patient_notes',
        'doctor_notes',
        'rejection_reason',
        'assigned_at',
        'prescribed_at',
        'completed_at',
        'rejected_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'prescribed_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the patient that owns the medication order.
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get the doctor assigned to the medication order.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the prescription associated with the medication order.
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the items for the medication order.
     */
    public function items()
    {
        return $this->hasMany(MedicationOrderItem::class);
    }

    /**
     * Assign a doctor to the medication order.
     */
    public function assignDoctor(User $doctor)
    {
        $previousStatus = $this->status;

        $this->doctor_id = $doctor->id;
        $this->status = self::STATUS_ASSIGNED;
        $this->assigned_at = now();
        $this->save();

        // Notify the doctor about the assignment
        $doctor->notify(new MedicationOrderAssigned($this));

        // Notify the patient about the status change
        $this->patient->notify(new MedicationOrderStatusChanged($this, $previousStatus));
    }

    /**
     * Link a prescription to the medication order.
     */
    public function linkPrescription(Prescription $prescription)
    {
        $previousStatus = $this->status;

        $this->prescription_id = $prescription->id;
        $this->status = self::STATUS_PRESCRIBED;
        $this->prescribed_at = now();
        $this->save();

        // Notify the patient about the status change
        $this->patient->notify(new MedicationOrderStatusChanged($this, $previousStatus));
    }

    /**
     * Mark the medication order as completed.
     */
    public function complete()
    {
        $previousStatus = $this->status;

        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();

        // Notify the patient about the status change
        $this->patient->notify(new MedicationOrderStatusChanged($this, $previousStatus));
    }

    /**
     * Reject the medication order.
     */
    public function reject($reason = null)
    {
        $previousStatus = $this->status;

        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        $this->rejected_at = now();
        $this->save();

        // Notify the patient about the status change
        $this->patient->notify(new MedicationOrderStatusChanged($this, $previousStatus));
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include assigned orders.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', self::STATUS_ASSIGNED);
    }

    /**
     * Scope a query to only include prescribed orders.
     */
    public function scopePrescribed($query)
    {
        return $query->where('status', self::STATUS_PRESCRIBED);
    }

    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include rejected orders.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
