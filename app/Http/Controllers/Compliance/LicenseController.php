<?php

namespace App\Http\Controllers\Compliance;

use App\Application\Compliance\Commands\VerifyProviderLicense;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\LicenseReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LicenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $providerId = $request->query('provider_id');
        $licenseType = $request->query('license_type');
        $status = $request->query('status', 'verified');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $query = LicenseReadModel::query();

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        if ($licenseType) {
            $query->where('license_type', $licenseType);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $licenses = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($licenses);
    }

    public function store(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'provider_id' => 'required|integer',
            'license_number' => 'required|string|unique:license_read_model',
            'license_type' => 'required|string|in:md,do,rn,pa,np,pharmacist',
            'expires_at' => 'nullable|date',
            'issuing_body' => 'nullable|string',
            'verification_url' => 'nullable|url',
        ]);

        $licenseUuid = (string) Str::uuid();

        $command = new VerifyProviderLicense(
            licenseUuid: $licenseUuid,
            providerId: $data['provider_id'],
            licenseNumber: $data['license_number'],
            licenseType: $data['license_type'],
            verifiedAt: now(),
            expiresAt: $data['expires_at'] ?? null,
            issuingBody: $data['issuing_body'] ?? null,
            verificationUrl: $data['verification_url'] ?? null,
            metadata: ['source' => 'api', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'license_uuid' => $licenseUuid,
            'message' => 'License verified successfully',
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $license = LicenseReadModel::where('license_uuid', $uuid)->first();

        if (! $license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        return response()->json($license);
    }
}

