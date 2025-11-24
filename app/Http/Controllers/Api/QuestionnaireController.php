<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionnaireReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    /**
     * Get questionnaire for signup flow.
     * 
     * Can filter by medication_id or condition_id.
     */
    public function index(Request $request): JsonResponse
    {
        $medicationId = $request->query('medication_id');
        $conditionId = $request->query('condition_id');

        $query = QuestionnaireReadModel::query()
            ->where('status', 'active');

        // For now, return a default questionnaire if no specific filters
        // In the future, this could be customized based on medication/condition
        if ($medicationId) {
            // Filter by medication if needed
            // $query->where('medication_id', $medicationId);
        }

        if ($conditionId) {
            // Filter by condition if needed
            // $query->where('condition_id', $conditionId);
        }

        $questionnaire = $query->first();

        if (!$questionnaire) {
            return response()->json([
                'data' => [],
            ]);
        }

        // Parse questions from JSON
        $questions = $questionnaire->questions ?? [];
        if (is_string($questions)) {
            $questions = json_decode($questions, true) ?? [];
        }

        return response()->json([
            'data' => $questions,
        ]);
    }
}

