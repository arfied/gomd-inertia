<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Condition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConditionController extends Controller
{
    /**
     * Get list of conditions for signup flow.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Condition::query();

        // Filter by search query
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('therapeutic_use', 'like', "%{$search}%");
        }

        // Paginate results
        $conditions = $query->select('id', 'name', 'description', 'therapeutic_use')->paginate(20);

        return response()->json([
            'data' => $conditions->items(),
            'pagination' => [
                'total' => $conditions->total(),
                'per_page' => $conditions->perPage(),
                'current_page' => $conditions->currentPage(),
                'last_page' => $conditions->lastPage(),
            ],
        ]);
    }

    /**
     * Get a single condition by ID.
     */
    public function show(Condition $condition): JsonResponse
    {
        return response()->json([
            'data' => $condition,
        ]);
    }
}

