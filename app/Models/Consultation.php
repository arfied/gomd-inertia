<?php

namespace App\Models;

use App\Concerns\FormatsDateAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory, FormatsDateAttributes;

    const STATUSES = [
        'pending' => 'Pending',
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'scheduled_at',
        'started_at',
        'ended_at',
        'status',
        'type',
        'patient_complaint',
        'doctor_notes',
        'diagnosis',
        'treatment_plan',
        'follow_up_instructions',
        'follow_up_date',
    ];

    protected $casts = [
        // 'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'follow_up_date' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    public function counselings()
    {
        return $this->hasMany(PatientCounseling::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->whereIn('status', ['completed', 'cancelled'])
                     ->orWhere('scheduled_at', '<', now());
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function start()
    {
        $this->status = 'in_progress';
        $this->started_at = now();
        $this->save();
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->ended_at = now();
        $this->save();
    }
}
