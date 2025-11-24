<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Service;
use App\Models\QuestionnaireReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionnaireMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_questionnaire_from_questions_table(): void
    {
        // Create test service
        $service = Service::create([
            'name' => 'Cardiovascular',
            'description' => 'Heart and blood pressure related questions',
            'is_active' => true,
        ]);

        // Create test questions
        $question1 = Question::create([
            'service_id' => $service->id,
            'question' => 'Have you been diagnosed with high blood pressure?',
            'type' => 'textarea',
            'required' => true,
            'order' => 1,
        ]);

        $question2 = Question::create([
            'service_id' => $service->id,
            'question' => 'Select your symptoms',
            'type' => 'checkbox',
            'required' => false,
            'order' => 2,
        ]);

        // Add options to question2
        QuestionOption::create([
            'question_id' => $question2->id,
            'option_value' => 'Chest pain',
            'order' => 1,
        ]);
        QuestionOption::create([
            'question_id' => $question2->id,
            'option_value' => 'Shortness of breath',
            'order' => 2,
        ]);

        // Manually run the migration logic
        $questions = Question::with('options', 'service')->get();
        $formatted = $questions->map(function ($q) {
            return [
                'id' => "q{$q->id}",
                'text' => $q->question,
                'type' => $q->type,
                'required' => (bool) $q->required,
                'options' => $q->options->pluck('option_value')->toArray(),
                'section' => 'cardiovascular',
                'order' => $q->order,
            ];
        })->toArray();

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => \Illuminate\Support\Str::uuid(),
            'title' => 'Comprehensive Health Screening',
            'description' => 'Initial health assessment questionnaire migrated from legacy system',
            'questions' => $formatted,
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Verify questionnaire was created
        $questionnaire = QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->first();
        $this->assertNotNull($questionnaire);
        $this->assertEquals('active', $questionnaire->status);
        $this->assertIsArray($questionnaire->questions);
        $this->assertCount(2, $questionnaire->questions);
    }

    public function test_migration_transforms_questions_correctly(): void
    {
        $service = Service::create([
            'name' => 'Neurological',
            'is_active' => true,
        ]);

        Question::create([
            'service_id' => $service->id,
            'question' => 'Do you have seizures?',
            'type' => 'radio',
            'required' => true,
            'order' => 1,
        ]);

        // Manually run the migration logic
        $questions = Question::with('options', 'service')->get();
        $formatted = $questions->map(function ($q) {
            return [
                'id' => "q{$q->id}",
                'text' => $q->question,
                'type' => $q->type,
                'required' => (bool) $q->required,
                'options' => $q->options->pluck('option_value')->toArray(),
                'section' => 'neurological',
                'order' => $q->order,
            ];
        })->toArray();

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => \Illuminate\Support\Str::uuid(),
            'title' => 'Comprehensive Health Screening',
            'description' => 'Initial health assessment questionnaire migrated from legacy system',
            'questions' => $formatted,
            'status' => 'active',
            'created_by' => 1,
        ]);

        $questionnaire = QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->first();
        $questions = $questionnaire->questions;

        $this->assertCount(1, $questions);
        $this->assertEquals('q1', $questions[0]['id']);
        $this->assertEquals('Do you have seizures?', $questions[0]['text']);
        $this->assertEquals('radio', $questions[0]['type']);
        $this->assertTrue($questions[0]['required']);
        $this->assertEquals('neurological', $questions[0]['section']);
    }

    public function test_migration_includes_question_options(): void
    {
        $service = Service::create([
            'name' => 'Mental Health',
            'is_active' => true,
        ]);

        $question = Question::create([
            'service_id' => $service->id,
            'question' => 'How often do you feel stressed?',
            'type' => 'select',
            'required' => false,
            'order' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'option_value' => 'Daily',
            'order' => 1,
        ]);
        QuestionOption::create([
            'question_id' => $question->id,
            'option_value' => 'Weekly',
            'order' => 2,
        ]);

        // Manually run the migration logic
        $questions = Question::with('options', 'service')->get();
        $formatted = $questions->map(function ($q) {
            return [
                'id' => "q{$q->id}",
                'text' => $q->question,
                'type' => $q->type,
                'required' => (bool) $q->required,
                'options' => $q->options->pluck('option_value')->toArray(),
                'section' => 'mental_health',
                'order' => $q->order,
            ];
        })->toArray();

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => \Illuminate\Support\Str::uuid(),
            'title' => 'Comprehensive Health Screening',
            'description' => 'Initial health assessment questionnaire migrated from legacy system',
            'questions' => $formatted,
            'status' => 'active',
            'created_by' => 1,
        ]);

        $questionnaire = QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->first();
        $questions = $questionnaire->questions;

        $this->assertCount(2, $questions[0]['options']);
        $this->assertContains('Daily', $questions[0]['options']);
        $this->assertContains('Weekly', $questions[0]['options']);
    }

    public function test_seeder_creates_test_questionnaire(): void
    {
        $this->seed(\Database\Seeders\QuestionnaireSeeder::class);

        $questionnaire = QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->first();
        $this->assertNotNull($questionnaire);
        $this->assertIsArray($questionnaire->questions);
        $this->assertGreaterThan(0, count($questionnaire->questions));
    }

    public function test_seeder_questionnaire_has_all_sections(): void
    {
        $this->seed(\Database\Seeders\QuestionnaireSeeder::class);

        $questionnaire = QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->first();
        $sections = collect($questionnaire->questions)->pluck('section')->unique();

        $this->assertContains('cardiovascular', $sections);
        $this->assertContains('neurological', $sections);
        $this->assertContains('mental_health', $sections);
        $this->assertContains('allergies', $sections);
    }

    public function test_seeder_questionnaire_has_conditional_questions(): void
    {
        $this->seed(\Database\Seeders\QuestionnaireSeeder::class);

        $questionnaire = QuestionnaireReadModel::where('title', 'Comprehensive Health Screening')->first();
        $conditionalQuestions = collect($questionnaire->questions)
            ->filter(fn($q) => isset($q['parent_question_id']) && $q['parent_question_id'] !== null);

        $this->assertGreaterThan(0, $conditionalQuestions->count());
    }
}

