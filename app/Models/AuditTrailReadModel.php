<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for audit trails.
 *
 * This materialized view is updated by event handlers listening to
 * audit log events. It provides optimized queries for compliance
 * reporting and investigation.
 */
class AuditTrailReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'audit_trail_read_model';

    protected $fillable = [
        'audit_uuid',
        'patient_id',
        'accessed_by',
        'access_type',
        'resource',
        'accessed_at',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get audit logs for a specific patient.
     */
    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId)->orderBy('accessed_at', 'desc');
    }

    /**
     * Get audit logs by a specific user.
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('accessed_by', $userId);
    }

    /**
     * Get audit logs of a specific access type.
     */
    public function scopeOfType($query, string $accessType)
    {
        return $query->where('access_type', $accessType);
    }

    /**
     * Get audit logs within a date range.
     */
    public function scopeInDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('accessed_at', [$startDate, $endDate]);
    }

    /**
     * Get suspicious access patterns.
     */
    public function scopeSuspicious($query)
    {
        return $query->where('access_type', 'export')
            ->orWhere('access_type', 'delete')
            ->orderBy('accessed_at', 'desc');
    }
}

