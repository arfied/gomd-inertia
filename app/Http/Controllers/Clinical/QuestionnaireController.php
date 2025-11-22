<?php

namespace App\Http\Controllers\Clinical;

use App\Application\Clinical\Commands\CreateQuestionnaire;
use App\Application\Clinical\Commands\SubmitQuestionnaireResponse;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\QuestionnaireReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionnaireController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patientId = $request->query('patient_id');
        $status = $request->query('status', 'active');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $query = QuestionnaireReadModel::query();

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $questionnaires = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($questionnaires);
    }

    public function store(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'nullable|array',
            'patient_id' => 'nullable|string',
        ]);

        $questionnaireUuid = (string) Str::uuid();

        $command = new CreateQuestionnaire(
            questionnaireUuid: $questionnaireUuid,
            title: $data['title'],
            description: $data['description'] ?? null,
            questions: $data['questions'] ?? [],
            createdBy: $request->user()?->id,
            patientId: $data['patient_id'] ?? null,
            metadata: ['source' => 'api', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'questionnaire_uuid' => $questionnaireUuid,
            'message' => 'Questionnaire created successfully',
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $uuid)->first();

        if (! $questionnaire) {
            return response()->json(['message' => 'Questionnaire not found'], 404);
        }

        return response()->json($questionnaire);
    }

    public function submitResponse(string $uuid, Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'responses' => 'required|array',
        ]);

        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $uuid)->first();

        if (! $questionnaire) {
            return response()->json(['message' => 'Questionnaire not found'], 404);
        }

        $command = new SubmitQuestionnaireResponse(
            questionnaireId: $uuid,
            responses: $data['responses'],
            submittedAt: now(),
            metadata: ['source' => 'api', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'message' => 'Response submitted successfully',
            'questionnaire_uuid' => $uuid,
        ], 200);
    }
}

