<?php

namespace App\Services\Compliance;

use App\Domain\Compliance\AuditLogAggregate;
use App\Services\EventStoreContract;
use Illuminate\Support\Str;

/**
 * Audit Trail Service
 *
 * Manages comprehensive audit logging for all patient data access
 * and modifications to ensure compliance with regulatory requirements.
 */
class AuditTrailService
{
    public function __construct(private EventStoreContract $eventStore)
    {
    }

    /**
     * Log data access to the audit trail.
     *
     * @param  string  $patientId
     * @param  string  $accessedBy
     * @param  string  $accessType
     * @param  string  $resource
     * @param  array<string, mixed>  $context
     */
    public function logAccess(
        string $patientId,
        string $accessedBy,
        string $accessType,
        string $resource,
        array $context = []
    ): void {
        $uuid = Str::uuid()->toString();

        $aggregate = AuditLogAggregate::create($uuid, [
            'patient_id' => $patientId,
            'accessed_by' => $accessedBy,
            'access_type' => $accessType,
            'resource' => $resource,
            'accessed_at' => now()->toIso8601String(),
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        $events = $aggregate->releaseEvents();
        foreach ($events as $event) {
            $this->eventStore->store($event);
        }
    }

    /**
     * Log data modification to the audit trail.
     *
     * @param  string  $patientId
     * @param  string  $modifiedBy
     * @param  string  $resource
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function logModification(
        string $patientId,
        string $modifiedBy,
        string $resource,
        array $oldValues,
        array $newValues
    ): void {
        $this->logAccess($patientId, $modifiedBy, 'write', $resource, [
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Log data export to the audit trail.
     *
     * @param  string  $patientId
     * @param  string  $exportedBy
     * @param  string  $exportFormat
     */
    public function logExport(string $patientId, string $exportedBy, string $exportFormat): void
    {
        $this->logAccess($patientId, $exportedBy, 'export', 'patient_record', [
            'export_format' => $exportFormat,
        ]);
    }

    /**
     * Log data deletion to the audit trail.
     *
     * @param  string  $patientId
     * @param  string  $deletedBy
     * @param  string  $resource
     * @param  array<string, mixed>  $deletedData
     */
    public function logDeletion(
        string $patientId,
        string $deletedBy,
        string $resource,
        array $deletedData
    ): void {
        $this->logAccess($patientId, $deletedBy, 'delete', $resource, [
            'deleted_data' => $deletedData,
        ]);
    }
}

