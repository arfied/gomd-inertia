<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for consultations.
 *
 * This materialized view is updated by event handlers listening to
 * consultation events. It provides optimized queries for consultation
 * scheduling, availability, and history.
 */
class ConsultationReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'consultation_read_model';

    protected $fillable = [
        'consultation_uuid',
        'patient_id',
        'doctor_id',
        'scheduled_at',
        'reason',
        'status',
        'notes',
        'completed_at',
        'created_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get consultations for a specific patient.
     */
    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId)->orderBy('scheduled_at', 'desc');
    }

    /**
     * Get consultations with a specific doctor.
     */
    public function scopeWithDoctor($query, string $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Get upcoming consultations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at', 'asc');
    }

    /**
     * Get completed consultations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
            ->orderBy('completed_at', 'desc');
    }
}

