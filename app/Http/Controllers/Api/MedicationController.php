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
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('generic_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by status - only show approved medications
        $query->where('status', 'approved');

        // Order by name
        $query->orderBy('name', 'asc');

        // Group by name to avoid duplicates
        $query->groupBy('name');

        // Paginate results
        $medications = $query->select('id', 'name', 'generic_name', 'description', 'dosage_form', 'strength')->paginate(20);

        return response()->json([
            'data' => $medications->items(),
            'pagination' => [
                'total' => $medications->total(),
                'per_page' => $medications->perPage(),
                'current_page' => $medications->currentPage(),
                'last_page' => $medications->lastPage(),
            ],
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

