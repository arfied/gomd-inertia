<?php

namespace App\Http\Controllers\Api;

use App\Application\Commands\CommandBus;
use App\Application\Questionnaire\Commands\SubmitQuestionnaireResponse;
use App\Http\Controllers\Controller;
use App\Models\QuestionnaireReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionnaireSubmissionController extends Controller
{
    /**
     * Submit questionnaire responses.
     *
     * This endpoint accepts questionnaire responses and stores them
     * via the event-sourced questionnaire system.
     */
    public function submit(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'questionnaire_uuid' => 'required|string',
            'patient_id' => 'nullable|string',
            'responses' => 'required|array',
        ]);

        // Find the questionnaire
        $questionnaire = QuestionnaireReadModel::where(
            'questionnaire_uuid',
            $data['questionnaire_uuid']
        )->first();

        if (!$questionnaire) {
            return response()->json([
                'success' => false,
                'message' => 'Questionnaire not found',
            ], 404);
        }

        // Validate responses against questionnaire questions
        $validationErrors = $this->validateResponses(
            $data['responses'],
            $questionnaire->questions ?? []
        );

        if (!empty($validationErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors,
            ], 422);
        }

        // Submit responses via command
        $command = new SubmitQuestionnaireResponse(
            questionnaireId: $data['questionnaire_uuid'],
            patientId: $data['patient_id'] ?? null,
            responses: $data['responses'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        try {
            $commandBus->dispatch($command);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit questionnaire: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Questionnaire submitted successfully',
            'questionnaire_uuid' => $data['questionnaire_uuid'],
        ]);
    }

    /**
     * Validate responses against questionnaire questions.
     */
    private function validateResponses(array $responses, array $questions): array
    {
        $errors = [];

        foreach ($questions as $question) {
            $questionId = $question['id'] ?? null;
            if (!$questionId) {
                continue;
            }

            $isRequired = $question['required'] ?? false;
            $response = $responses[$questionId] ?? null;

            // Check required fields
            if ($isRequired && (is_null($response) || $response === '' || (is_array($response) && empty($response)))) {
                $errors[$questionId] = 'This field is required';
                continue;
            }

            // Validate based on question type
            $type = $question['type'] ?? 'text';
            $typeError = $this->validateQuestionType($response, $type, $question);
            if ($typeError) {
                $errors[$questionId] = $typeError;
            }
        }

        return $errors;
    }

    /**
     * Validate response based on question type.
     */
    private function validateQuestionType($response, string $type, array $question): ?string
    {
        if (is_null($response) || $response === '') {
            return null;
        }

        switch ($type) {
            case 'number':
                if (!is_numeric($response)) {
                    return 'Must be a valid number';
                }
                break;

            case 'date':
                if (!strtotime($response)) {
                    return 'Must be a valid date';
                }
                break;

            case 'checkbox':
            case 'radio':
                $options = $question['options'] ?? [];
                $validOptions = array_map(
                    fn($opt) => is_array($opt) ? $opt['value'] : $opt,
                    $options
                );

                if (is_array($response)) {
                    foreach ($response as $val) {
                        if (!in_array($val, $validOptions)) {
                            return 'Invalid option selected';
                        }
                    }
                } else {
                    if (!in_array($response, $validOptions)) {
                        return 'Invalid option selected';
                    }
                }
                break;

            case 'select':
                $options = $question['options'] ?? [];
                $validOptions = array_map(
                    fn($opt) => is_array($opt) ? $opt['value'] : $opt,
                    $options
                );

                if (!in_array($response, $validOptions)) {
                    return 'Invalid option selected';
                }
                break;
        }

        return null;
    }
}

