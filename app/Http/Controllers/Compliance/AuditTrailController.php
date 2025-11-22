<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\AuditTrailReadModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditTrailController extends Controller
{
    public function index(Request $request): Response
    {
        $patientId = $request->query('patient_id');
        $accessedBy = $request->query('accessed_by');
        $accessType = $request->query('access_type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 25);

        $query = AuditTrailReadModel::query();

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($accessedBy) {
            $query->where('accessed_by', $accessedBy);
        }

        if ($accessType) {
            $query->where('access_type', $accessType);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('accessed_at', [$startDate, $endDate]);
        }

        $auditTrail = $query->orderBy('accessed_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return Inertia::render('compliance/AuditTrail', [
            'auditTrail' => $auditTrail,
        ]);
    }

    public function show(string $uuid): Response
    {
        $audit = AuditTrailReadModel::where('audit_uuid', $uuid)->first();

        if (! $audit) {
            abort(404, 'Audit record not found');
        }

        return Inertia::render('compliance/AuditTrail', [
            'auditTrail' => [$audit],
            'selectedAudit' => $audit,
        ]);
    }

    public function export(Request $request)
    {
        $patientId = $request->query('patient_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = AuditTrailReadModel::query();

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('accessed_at', [$startDate, $endDate]);
        }

        $records = $query->orderBy('accessed_at', 'desc')->get();

        return response()->json([
            'total_records' => $records->count(),
            'records' => $records,
            'exported_at' => now(),
        ]);
    }
}

