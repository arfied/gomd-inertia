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
     * Can filter by medication_name or condition_id.
     */
    public function index(Request $request): JsonResponse
    {
        $medicationName = $request->query('medication_name');
        $conditionId = $request->query('condition_id');

        $query = QuestionnaireReadModel::query()
            ->where('status', 'active');

        // For now, return a default questionnaire if no specific filters
        // In the future, this could be customized based on medication/condition
        if ($medicationName) {
            // Filter by medication if needed
            // $query->where('medication_name', $medicationName);
        }

        if ($conditionId) {
            // Filter by condition if needed
            // $query->where('condition_id', $conditionId);
        }

        $questionnaire = $query->first();

        if (!$questionnaire) {
            return response()->json([
                'data' => [],
                'questionnaire_uuid' => null,
            ]);
        }

        // Parse questions from JSON
        $questions = $questionnaire->questions ?? [];
        if (is_string($questions)) {
            $questions = json_decode($questions, true) ?? [];
        }

        return response()->json([
            'data' => $questions,
            'questionnaire_uuid' => $questionnaire->questionnaire_uuid,
        ]);
    }
}

