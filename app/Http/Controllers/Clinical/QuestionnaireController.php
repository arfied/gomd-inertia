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
use Inertia\Inertia;
use Inertia\Response;

class QuestionnaireController extends Controller
{
    public function index(Request $request): Response
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

        return Inertia::render('clinical/Questionnaires', [
            'questionnaires' => $questionnaires,
        ]);
    }

    public function store(Request $request, CommandBus $commandBus)
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
            metadata: ['source' => 'web', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return redirect()->route('clinical.questionnaires.index')
            ->with('success', 'Questionnaire created successfully');
    }

    public function show(string $uuid): Response
    {
        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $uuid)->first();

        if (! $questionnaire) {
            abort(404, 'Questionnaire not found');
        }

        return Inertia::render('clinical/Questionnaires', [
            'questionnaires' => [$questionnaire],
            'selectedQuestionnaire' => $questionnaire,
        ]);
    }

    public function submitResponse(string $uuid, Request $request, CommandBus $commandBus)
    {
        $data = $request->validate([
            'responses' => 'required|array',
        ]);

        $questionnaire = QuestionnaireReadModel::where('questionnaire_uuid', $uuid)->first();

        if (! $questionnaire) {
            abort(404, 'Questionnaire not found');
        }

        $command = new SubmitQuestionnaireResponse(
            questionnaireId: $uuid,
            responses: $data['responses'],
            submittedAt: now(),
            metadata: ['source' => 'web', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return redirect()->route('clinical.questionnaires.index')
            ->with('success', 'Response submitted successfully');
    }
}

