<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    const CURRENT_GROUP_OFFERED = 3;
    /**
     * Get list of subscription plans for signup flow.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubscriptionPlan::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $query->where('is_active', true);

        $query->where('group_id', self::CURRENT_GROUP_OFFERED);

        $query->orderBy('display_order', 'asc');

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

