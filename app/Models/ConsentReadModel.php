<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for patient consents.
 *
 * This materialized view is updated by event handlers listening to
 * consent events. It provides optimized queries for consent verification
 * and compliance checks.
 */
class ConsentReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'consent_read_model';

    protected $fillable = [
        'consent_uuid',
        'patient_id',
        'consent_type',
        'granted_by',
        'granted_at',
        'expires_at',
        'terms_version',
        'status',
        'created_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get active consents for a patient.
     */
    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId)
            ->where('status', 'active');
    }

    /**
     * Get consents of a specific type.
     */
    public function scopeOfType($query, string $consentType)
    {
        return $query->where('consent_type', $consentType);
    }

    /**
     * Get expired consents.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
            ->where('status', 'active');
    }

    /**
     * Get consents expiring soon.
     */
    public function scopeExpiringsoon($query, int $days = 30)
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->where('status', 'active');
    }
}

