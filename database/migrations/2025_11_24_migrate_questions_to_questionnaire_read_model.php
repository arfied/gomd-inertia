<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use App\Models\Question;
use App\Models\QuestionnaireReadModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all questions with their options
        $questions = Question::with('options', 'service')->get();

        if ($questions->isEmpty()) {
            return;
        }

        // Transform questions to new format
        $formatted = $questions->map(function ($q) {
            return [
                'id' => "q{$q->id}",
                'text' => $q->question,
                'type' => $q->type,
                'required' => (bool) $q->required,
                'options' => $q->options->pluck('option_value')->toArray(),
                'section' => $this->mapServiceToSection($q->service_id, $q->service?->name),
                'order' => $q->order,
                'parent_question_id' => $q->parent_question_id ? "q{$q->parent_question_id}" : null,
                'parent_answer_value' => $q->parent_answer_value,
            ];
        })->toArray();

        // Create questionnaire in read model
        QuestionnaireReadModel::create([
            'questionnaire_uuid' => (string) Str::uuid(),
            'title' => 'Comprehensive Health Screening',
            'description' => 'Initial health assessment questionnaire migrated from legacy system',
            'questions' => $formatted,
            'status' => 'active',
            'created_by' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the migrated questionnaire
        QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->delete();
    }

    /**
     * Map service ID/name to section name
     */
    private function mapServiceToSection(?int $serviceId, ?string $serviceName): string
    {
        $mapping = [
            1 => 'cardiovascular',
            2 => 'neurological',
            3 => 'gastrointestinal',
            4 => 'endocrine',
            5 => 'preventive_care',
            6 => 'infection_prevention',
            7 => 'dermatological',
            8 => 'immunology',
            9 => 'mental_health',
            10 => 'pain',
            11 => 'respiratory',
            12 => 'prevention',
            13 => 'weight_management',
            14 => 'additional_information',
            15 => 'current_medication',
            16 => 'allergies',
        ];

        if ($serviceId && isset($mapping[$serviceId])) {
            return $mapping[$serviceId];
        }

        // Fallback: try to map by service name
        if ($serviceName) {
            $name = strtolower($serviceName);
            if (str_contains($name, 'cardio')) return 'cardiovascular';
            if (str_contains($name, 'neuro')) return 'neurological';
            if (str_contains($name, 'gastro')) return 'gastrointestinal';
            if (str_contains($name, 'endo')) return 'endocrine';
            if (str_contains($name, 'mental')) return 'mental_health';
            if (str_contains($name, 'pain')) return 'pain';
            if (str_contains($name, 'respir')) return 'respiratory';
            if (str_contains($name, 'weight')) return 'weight_management';
            if (str_contains($name, 'allerg')) return 'allergies';
            if (str_contains($name, 'derma')) return 'dermatological';
        }

        return 'general';
    }
};

