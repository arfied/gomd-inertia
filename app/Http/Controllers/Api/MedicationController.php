<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    /**
     * Get list of medications for signup flow.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Medication::query();

        // Filter by search query
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('generic_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by status - only show approved medications
        $query->where('status', 'approved');

        // Get one medication per unique generic_name using subquery
        $subquery = $query->selectRaw('MIN(id) as id')
            ->groupBy('generic_name');

        // Now get the full medication details for those IDs
        $medications = Medication::whereIn('id', $subquery)
            ->select('id', 'generic_name', 'description')
            ->orderBy('generic_name', 'asc')
            ->get();

        return response()->json([
            'data' => $medications,
        ]);
    }

    /**
     * Get a single medication by ID.
     */
    public function show(Medication $medication): JsonResponse
    {
        return response()->json([
            'data' => $medication,
        ]);
    }
}

