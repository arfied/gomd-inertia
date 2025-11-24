<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Get list of subscription plans for signup flow.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubscriptionPlan::query();

        // Filter by search query
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by active status
        $query->where('is_active', true);

        // Order by display order
        $query->orderBy('display_order', 'asc');

        // Paginate results using simplePaginate
        $plans = $query->select('id', 'name', 'price', 'duration_months', 'features', 'benefits', 'is_featured')->simplePaginate(20);

        return response()->json([
            'data' => $plans->items(),
            'pagination' => [
                'per_page' => $plans->perPage(),
                'current_page' => $plans->currentPage(),
                'next_page_url' => $plans->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get a single subscription plan by ID.
     */
    public function show(SubscriptionPlan $plan): JsonResponse
    {
        return response()->json([
            'data' => $plan,
        ]);
    }
}

