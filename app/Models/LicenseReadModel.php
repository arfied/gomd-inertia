<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read model for healthcare provider licenses.
 *
 * This materialized view is updated by event handlers listening to
 * license events. It provides optimized queries for license verification
 * and compliance checks.
 */
class LicenseReadModel extends Model
{
    public $timestamps = false;
    protected $table = 'license_read_model';

    protected $fillable = [
        'license_uuid',
        'provider_id',
        'license_number',
        'license_type',
        'verified_at',
        'expires_at',
        'issuing_body',
        'status',
        'verification_url',
        'created_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get licenses for a specific provider.
     */
    public function scopeForProvider($query, string $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Get verified licenses.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Get expired licenses.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Get licenses expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->where('status', 'verified');
    }

    /**
     * Get licenses by type.
     */
    public function scopeOfType($query, string $licenseType)
    {
        return $query->where('license_type', $licenseType);
    }
}

