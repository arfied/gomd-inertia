<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\QuestionnaireReadModel;

class QuestionnaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a comprehensive test questionnaire
        $questions = [
            // Cardiovascular section
            [
                'id' => 'q1',
                'text' => 'Have you been diagnosed with high blood pressure, high cholesterol, or heart disease?',
                'type' => 'textarea',
                'required' => true,
                'section' => 'cardiovascular',
                'order' => 1,
                'options' => [],
            ],
            [
                'id' => 'q2',
                'text' => 'Have you experienced chest pain, shortness of breath, or irregular heartbeat recently?',
                'type' => 'textarea',
                'required' => true,
                'section' => 'cardiovascular',
                'order' => 2,
                'options' => [],
            ],
            [
                'id' => 'q3',
                'text' => 'List all current heart/blood pressure medications',
                'type' => 'textarea',
                'required' => false,
                'section' => 'cardiovascular',
                'order' => 3,
                'options' => [],
            ],
            // Neurological section
            [
                'id' => 'q4',
                'text' => 'Describe any diagnosed neurological conditions (seizures, RLS, etc.)',
                'type' => 'textarea',
                'required' => false,
                'section' => 'neurological',
                'order' => 1,
                'options' => [],
            ],
            [
                'id' => 'q5',
                'text' => 'How often do you experience symptoms?',
                'type' => 'select',
                'required' => false,
                'section' => 'neurological',
                'order' => 2,
                'options' => ['Daily', 'Weekly', 'Monthly', 'Rarely', 'Never'],
            ],
            // Mental Health section
            [
                'id' => 'q6',
                'text' => 'Have you been diagnosed with depression or anxiety?',
                'type' => 'radio',
                'required' => true,
                'section' => 'mental_health',
                'order' => 1,
                'options' => ['Yes', 'No', 'Prefer not to say'],
            ],
            [
                'id' => 'q7',
                'text' => 'Which of the following apply to you?',
                'type' => 'checkbox',
                'required' => false,
                'section' => 'mental_health',
                'order' => 2,
                'options' => ['Stress', 'Anxiety', 'Depression', 'Sleep issues', 'Other'],
            ],
            // Allergies section
            [
                'id' => 'q8',
                'text' => 'Do you have any known drug allergies?',
                'type' => 'textarea',
                'required' => true,
                'section' => 'allergies',
                'order' => 1,
                'options' => [],
            ],
            // Conditional question
            [
                'id' => 'q9',
                'text' => 'Please describe your allergy symptoms',
                'type' => 'textarea',
                'required' => false,
                'section' => 'allergies',
                'order' => 2,
                'options' => [],
                'parent_question_id' => 'q8',
                'parent_answer_value' => ['Yes'],
            ],
        ];

        QuestionnaireReadModel::create([
            'questionnaire_uuid' => (string) Str::uuid(),
            'title' => 'Comprehensive Health Screening',
            'description' => 'Initial health assessment questionnaire for signup flow',
            'questions' => $questions,
            'status' => 'active',
            'created_by' => 1,
        ]);
    }
}

