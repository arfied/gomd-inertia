<?php

namespace App\Http\Controllers\Compliance;

use App\Application\Compliance\Commands\GrantConsent;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\ConsentReadModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ConsentController extends Controller
{
    public function index(Request $request): Response
    {
        $patientId = $request->query('patient_id');
        $consentType = $request->query('consent_type');
        $status = $request->query('status', 'active');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $query = ConsentReadModel::query();

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($consentType) {
            $query->where('consent_type', $consentType);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $consents = $query->paginate($perPage, ['*'], 'page', $page);

        return Inertia::render('compliance/Dashboard', [
            'consents' => $consents,
        ]);
    }

    public function store(Request $request, CommandBus $commandBus)
    {
        $data = $request->validate([
            'patient_id' => 'required|string',
            'consent_type' => 'required|string|in:treatment,research,data_sharing,marketing',
            'expires_at' => 'nullable|date',
            'terms_version' => 'nullable|string',
        ]);

        $consentUuid = (string) Str::uuid();

        $command = new GrantConsent(
            consentUuid: $consentUuid,
            patientId: $data['patient_id'],
            consentType: $data['consent_type'],
            grantedBy: $request->user()?->id,
            grantedAt: now(),
            expiresAt: $data['expires_at'] ?? null,
            termsVersion: $data['terms_version'] ?? null,
            metadata: ['source' => 'web', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return redirect()->route('compliance.dashboard')
            ->with('success', 'Consent granted successfully');
    }

    public function show(string $uuid): Response
    {
        $consent = ConsentReadModel::where('consent_uuid', $uuid)->first();

        if (! $consent) {
            abort(404, 'Consent not found');
        }

        return Inertia::render('compliance/Dashboard', [
            'consents' => [$consent],
            'selectedConsent' => $consent,
        ]);
    }
}

