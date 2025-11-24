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
        // Get unique medication names first
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

        // Get one medication per unique name using subquery
        $subquery = $query->selectRaw('MIN(id) as id')
            ->groupBy('name');

        // Now get the full medication details for those IDs
        $medications = Medication::whereIn('id', $subquery)
            ->select('id', 'name', 'generic_name', 'description', 'dosage_form', 'strength')
            ->orderBy('name', 'asc')
            ->simplePaginate(20);

        return response()->json([
            'data' => $medications->items(),
            'pagination' => [
                // 'total' => $medications->total(),
                'per_page' => $medications->perPage(),
                'current_page' => $medications->currentPage(),
                'next_page_url' => $medications->nextPageUrl(),
                // 'last_page' => $medications->lastPage(),
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

