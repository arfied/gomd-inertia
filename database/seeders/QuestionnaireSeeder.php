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
            // General Health Questions (no medication/condition filter)
            [
                'id' => 'q1',
                'text' => 'What is your date of birth?',
                'type' => 'date',
                'required' => true,
                'section' => 'general',
                'order' => 1,
                'options' => [],
                'medication_names' => [],
                'condition_id' => null,
            ],
            [
                'id' => 'q2',
                'text' => 'Do you have any known drug allergies?',
                'type' => 'textarea',
                'required' => true,
                'section' => 'general',
                'order' => 2,
                'options' => [],
                'medication_names' => [],
                'condition_id' => null,
            ],
            [
                'id' => 'q3',
                'text' => 'Please describe your allergy symptoms',
                'type' => 'textarea',
                'required' => false,
                'section' => 'general',
                'order' => 3,
                'options' => [],
                'parent_question_id' => 'q2',
                'parent_answer_value' => 'Yes',
                'medication_names' => [],
                'condition_id' => null,
            ],
            // Cardiovascular medication-specific questions
            [
                'id' => 'q4',
                'text' => 'Have you been diagnosed with high blood pressure, high cholesterol, or heart disease?',
                'type' => 'textarea',
                'required' => true,
                'section' => 'cardiovascular',
                'order' => 1,
                'options' => [],
                'medication_names' => ['Lisinopril', 'Metoprolol', 'Atorvastatin'],
                'condition_id' => null,
            ],
            [
                'id' => 'q5',
                'text' => 'Have you experienced chest pain, shortness of breath, or irregular heartbeat recently?',
                'type' => 'textarea',
                'required' => true,
                'section' => 'cardiovascular',
                'order' => 2,
                'options' => [],
                'medication_names' => ['Lisinopril', 'Metoprolol', 'Atorvastatin'],
                'condition_id' => null,
            ],
            // Neurological medication-specific questions
            [
                'id' => 'q6',
                'text' => 'Describe any diagnosed neurological conditions (seizures, RLS, etc.)',
                'type' => 'textarea',
                'required' => false,
                'section' => 'neurological',
                'order' => 1,
                'options' => [],
                'medication_names' => ['Gabapentin', 'Pregabalin'],
                'condition_id' => null,
            ],
            [
                'id' => 'q7',
                'text' => 'How often do you experience symptoms?',
                'type' => 'select',
                'required' => false,
                'section' => 'neurological',
                'order' => 2,
                'options' => ['Daily', 'Weekly', 'Monthly', 'Rarely', 'Never'],
                'medication_names' => ['Gabapentin', 'Pregabalin'],
                'condition_id' => null,
            ],
            // Mental Health medication-specific questions
            [
                'id' => 'q8',
                'text' => 'Have you been diagnosed with depression or anxiety?',
                'type' => 'radio',
                'required' => true,
                'section' => 'mental_health',
                'order' => 1,
                'options' => ['Yes', 'No', 'Prefer not to say'],
                'medication_names' => ['Sertraline', 'Escitalopram', 'Alprazolam'],
                'condition_id' => null,
            ],
            [
                'id' => 'q9',
                'text' => 'Which of the following apply to you?',
                'type' => 'checkbox',
                'required' => false,
                'section' => 'mental_health',
                'order' => 2,
                'options' => ['Stress', 'Anxiety', 'Depression', 'Sleep issues', 'Other'],
                'medication_names' => ['Sertraline', 'Escitalopram', 'Alprazolam'],
                'condition_id' => null,
            ],
            // Pain medication-specific questions
            [
                'id' => 'q10',
                'text' => 'What type of pain do you typically experience?',
                'type' => 'checkbox',
                'required' => true,
                'section' => 'pain',
                'order' => 1,
                'options' => ['Headache', 'Muscle pain', 'Joint pain', 'Back pain', 'Menstrual cramps', 'Other'],
                'medication_names' => ['Acetaminophen', 'Ibuprofen', 'Naproxen'],
                'condition_id' => null,
            ],
            [
                'id' => 'q11',
                'text' => 'How often do you experience pain?',
                'type' => 'select',
                'required' => true,
                'section' => 'pain',
                'order' => 2,
                'options' => ['Daily', 'Several times a week', 'Weekly', 'Monthly', 'Rarely'],
                'medication_names' => ['Acetaminophen', 'Ibuprofen', 'Naproxen'],
                'condition_id' => null,
            ],
            [
                'id' => 'q12',
                'text' => 'Do you have any stomach or digestive issues?',
                'type' => 'radio',
                'required' => true,
                'section' => 'pain',
                'order' => 3,
                'options' => ['Yes', 'No'],
                'medication_names' => ['Acetaminophen', 'Ibuprofen', 'Naproxen'],
                'condition_id' => null,
            ],
            [
                'id' => 'q13',
                'text' => 'Do you have liver disease or take other medications that affect the liver?',
                'type' => 'radio',
                'required' => true,
                'section' => 'pain',
                'order' => 4,
                'options' => ['Yes', 'No', 'Unsure'],
                'medication_names' => ['Acetaminophen'],
                'condition_id' => null,
            ],
            [
                'id' => 'q14',
                'text' => 'Do you have kidney disease or high blood pressure?',
                'type' => 'radio',
                'required' => true,
                'section' => 'pain',
                'order' => 5,
                'options' => ['Yes', 'No', 'Unsure'],
                'medication_names' => ['Ibuprofen', 'Naproxen'],
                'condition_id' => null,
            ],
            // General medication history questions (for all medications including Acyclovir)
            [
                'id' => 'q92',
                'text' => 'Are you currently taking other prescription medication, over-the-counter medication, supplements or herbal remedies?',
                'type' => 'radio',
                'required' => true,
                'section' => 'medication_history',
                'order' => 1,
                'options' => ['Yes', 'No'],
                'medication_names' => [],
                'condition_id' => null,
            ],
            [
                'id' => 'q96',
                'text' => 'Please list all prescription medications, over-the-counter medications, supplements, or herbal remedies you are currently taking. Include the exact name and dosage as listed on the prescription bottle.',
                'type' => 'textarea',
                'required' => false,
                'section' => 'medication_history',
                'order' => 2,
                'options' => [],
                'parent_question_id' => 'q92',
                'parent_answer_value' => 'Yes',
                'medication_names' => [],
                'condition_id' => null,
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

