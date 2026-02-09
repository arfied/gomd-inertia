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
     * Returns all questions from the active questionnaire.
     * Note: Questions are currently general health questions without medication/condition filtering.
     * Filtering logic can be added once questions have medication_names and condition_id metadata.
     */
    public function index(Request $request): JsonResponse
    {
        $query = QuestionnaireReadModel::query()
            ->where('status', 'active');

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

