<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for clinical notes.
 *
 * This materialized view is updated by event handlers listening to
 * clinical note events. It provides optimized queries for clinical
 * note retrieval and patient medical history.
 */
class ClinicalNoteReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'clinical_note_read_model';

    protected $fillable = [
        'clinical_note_uuid',
        'patient_id',
        'doctor_id',
        'content',
        'note_type',
        'recorded_at',
        'attachments',
        'created_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get clinical notes for a specific patient.
     */
    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId)->orderBy('recorded_at', 'desc');
    }

    /**
     * Get clinical notes by a specific doctor.
     */
    public function scopeByDoctor($query, string $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Get clinical notes of a specific type.
     */
    public function scopeOfType($query, string $noteType)
    {
        return $query->where('note_type', $noteType);
    }

    /**
     * Get recent clinical notes.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}

