<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Medication;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the medications table exists
        if (!Schema::hasTable('medications')) {
            return;
        }

        // Check if the is_usual_dosage column exists, add it if it doesn't
        if (!Schema::hasColumn('medications', 'is_usual_dosage')) {
            Schema::table('medications', function (Blueprint $table) {
                $table->boolean('is_usual_dosage')->default(false)->after('status');
            });
        }

        // Check if the order column exists, add it if it doesn't
        if (!Schema::hasColumn('medications', 'order')) {
            Schema::table('medications', function (Blueprint $table) {
                $table->integer('order')->default(0)->after('is_usual_dosage');
            });
        }

        // First batch of medications
        $medications = [
            // Acetaminophen
            [
                'name' => 'Acetaminophen',
                'generic_name' => 'Acetaminophen',
                'drug_class' => 'Analgesics',
                'description' => 'Used to treat mild to moderate pain and reduce fever.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '325mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.10,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Acetaminophen',
                'generic_name' => 'Acetaminophen',
                'drug_class' => 'Analgesics',
                'description' => 'Used to treat mild to moderate pain and reduce fever.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.15,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Acetaminophen',
                'generic_name' => 'Acetaminophen',
                'drug_class' => 'Analgesics',
                'description' => 'Used to treat mild to moderate pain and reduce fever.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '650mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.20,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],

            // Albuterol
            [
                'name' => 'Albuterol',
                'generic_name' => 'Albuterol',
                'drug_class' => 'Short-Acting Beta-2 Agonists',
                'description' => 'Used to treat or prevent bronchospasm in people with asthma, bronchitis, emphysema, and other lung diseases.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '90mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 25.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Albuterol',
                'generic_name' => 'Albuterol',
                'drug_class' => 'Short-Acting Beta-2 Agonists',
                'description' => 'Used to treat or prevent bronchospasm in people with asthma, bronchitis, emphysema, and other lung diseases.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Inhalation',
                'strength' => '0.083%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Albuterol',
                'generic_name' => 'Albuterol',
                'drug_class' => 'Short-Acting Beta-2 Agonists',
                'description' => 'Used to treat or prevent bronchospasm in people with asthma, bronchitis, emphysema, and other lung diseases.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Albuterol',
                'generic_name' => 'Albuterol',
                'drug_class' => 'Short-Acting Beta-2 Agonists',
                'description' => 'Used to treat or prevent bronchospasm in people with asthma, bronchitis, emphysema, and other lung diseases.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '4mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.75,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],

            // Alendronate
            [
                'name' => 'Alendronate',
                'generic_name' => 'Alendronate',
                'drug_class' => 'Bisphosphonates',
                'description' => 'Used to treat and prevent osteoporosis in postmenopausal women and to increase bone mass in men with osteoporosis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Alendronate',
                'generic_name' => 'Alendronate',
                'drug_class' => 'Bisphosphonates',
                'description' => 'Used to treat and prevent osteoporosis in postmenopausal women and to increase bone mass in men with osteoporosis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Alendronate',
                'generic_name' => 'Alendronate',
                'drug_class' => 'Bisphosphonates',
                'description' => 'Used to treat and prevent osteoporosis in postmenopausal women and to increase bone mass in men with osteoporosis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '35mg',
                'manufacturer' => 'Various',
                'unit_price' => 3.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Alendronate',
                'generic_name' => 'Alendronate',
                'drug_class' => 'Bisphosphonates',
                'description' => 'Used to treat and prevent osteoporosis in postmenopausal women and to increase bone mass in men with osteoporosis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '70mg',
                'manufacturer' => 'Various',
                'unit_price' => 4.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 4
            ],

            // Amlodipine
            [
                'name' => 'Amlodipine',
                'generic_name' => 'Amlodipine',
                'drug_class' => 'Calcium Channel Blockers',
                'description' => 'Used to treat high blood pressure and chest pain (angina).',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2.5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.25,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Amlodipine',
                'generic_name' => 'Amlodipine',
                'drug_class' => 'Calcium Channel Blockers',
                'description' => 'Used to treat high blood pressure and chest pain (angina).',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Amlodipine',
                'generic_name' => 'Amlodipine',
                'drug_class' => 'Calcium Channel Blockers',
                'description' => 'Used to treat high blood pressure and chest pain (angina).',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.35,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],

            // Amoxicillin_Clavulanate
            [
                'name' => 'Amoxicillin-Clavulanate',
                'generic_name' => 'Amoxicillin-Clavulanate',
                'drug_class' => 'Penicillin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '250mg-125mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Amoxicillin-Clavulanate',
                'generic_name' => 'Amoxicillin-Clavulanate',
                'drug_class' => 'Penicillin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg-125mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Amoxicillin-Clavulanate',
                'generic_name' => 'Amoxicillin-Clavulanate',
                'drug_class' => 'Penicillin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '875mg-125mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Amoxicillin-Clavulanate',
                'generic_name' => 'Amoxicillin-Clavulanate',
                'drug_class' => 'Penicillin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '200mg-28.5mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Refrigerate after reconstitution.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Amoxicillin-Clavulanate',
                'generic_name' => 'Amoxicillin-Clavulanate',
                'drug_class' => 'Penicillin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '400mg-57mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Refrigerate after reconstitution.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ]
        ];

        foreach ($medications as $medication) {
            Medication::create($medication);
        }

        // Second batch of medications
        $medications2 = [
            // Amphotericin B
            [
                'name' => 'Amphotericin B',
                'generic_name' => 'Amphotericin B',
                'drug_class' => 'Antifungals',
                'description' => 'Used to treat serious, potentially life-threatening fungal infections.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intravenous',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 50.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at 2-8°C (36-46°F).',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Amphotericin B Liposomal',
                'generic_name' => 'Amphotericin B',
                'drug_class' => 'Antifungals',
                'description' => 'Liposomal formulation used to treat serious, potentially life-threatening fungal infections with reduced toxicity.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intravenous',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 150.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at 2-8°C (36-46°F).',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Apixaban
            [
                'name' => 'Apixaban',
                'generic_name' => 'Apixaban',
                'drug_class' => 'Direct Oral Anticoagulants',
                'description' => 'Used to prevent blood clots in patients with atrial fibrillation and to treat deep vein thrombosis and pulmonary embolism.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2.5mg',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Apixaban',
                'generic_name' => 'Apixaban',
                'drug_class' => 'Direct Oral Anticoagulants',
                'description' => 'Used to prevent blood clots in patients with atrial fibrillation and to treat deep vein thrombosis and pulmonary embolism.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 6.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],

            // Aripiprazole
            [
                'name' => 'Aripiprazole',
                'generic_name' => 'Aripiprazole',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and depression.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Aripiprazole',
                'generic_name' => 'Aripiprazole',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and depression.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 3.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Aripiprazole',
                'generic_name' => 'Aripiprazole',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and depression.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 4.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 3
            ],
            [
                'name' => 'Aripiprazole',
                'generic_name' => 'Aripiprazole',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and depression.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '15mg',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Aripiprazole',
                'generic_name' => 'Aripiprazole',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and depression.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '30mg',
                'manufacturer' => 'Various',
                'unit_price' => 6.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],

            // Atomoxetine
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 3.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '18mg',
                'manufacturer' => 'Various',
                'unit_price' => 3.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 4.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 3
            ],
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '40mg',
                'manufacturer' => 'Various',
                'unit_price' => 4.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '60mg',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '80mg',
                'manufacturer' => 'Various',
                'unit_price' => 5.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],
            [
                'name' => 'Atomoxetine',
                'generic_name' => 'Atomoxetine',
                'drug_class' => 'Norepinephrine Reuptake Inhibitors',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 6.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 7
            ],

            // Atorvastatin
            [
                'name' => 'Atorvastatin',
                'generic_name' => 'Atorvastatin',
                'drug_class' => 'HMG-CoA Reductase Inhibitors (Statins)',
                'description' => 'Used to lower cholesterol and reduce the risk of heart disease.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Atorvastatin',
                'generic_name' => 'Atorvastatin',
                'drug_class' => 'HMG-CoA Reductase Inhibitors (Statins)',
                'description' => 'Used to lower cholesterol and reduce the risk of heart disease.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '20mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.75,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Atorvastatin',
                'generic_name' => 'Atorvastatin',
                'drug_class' => 'HMG-CoA Reductase Inhibitors (Statins)',
                'description' => 'Used to lower cholesterol and reduce the risk of heart disease.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '40mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Atorvastatin',
                'generic_name' => 'Atorvastatin',
                'drug_class' => 'HMG-CoA Reductase Inhibitors (Statins)',
                'description' => 'Used to lower cholesterol and reduce the risk of heart disease.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '80mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.25,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ]
        ];

        foreach ($medications2 as $medication) {
            Medication::create($medication);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added medications
        $genericNames = [
            'Acetaminophen', 'Albuterol', 'Alendronate', 'Amlodipine', 'Amoxicillin-Clavulanate',
            'Amphotericin B', 'Apixaban', 'Aripiprazole', 'Atomoxetine', 'Atorvastatin',
            'Avanafil', 'Azithromycin', 'Brivaracetam', 'Butalbital-APAP-Caffeine', 'Carbamazepine',
            'Carvedilol', 'Ceftriaxone', 'Cetirizine', 'Chlorzoxazone', 'Ciprofloxacin',
            'Citalopram', 'Clobetasol', 'Clonidine', 'Dantrolene', 'Diltiazem',
            'Doxycycline', 'Dutasteride', 'Epinephrine', 'Estradiol', 'Ethosuximide',
            'Felbamate', 'Fexofenadine', 'Fluconazole', 'Fluticasone', 'Furosemide',
            'Guanfacine', 'Hydrochlorothiazide', 'Hydrocortisone', 'Ipratropium', 'Isosorbide',
            'Ketoconazole', 'Lamotrigine', 'Levetiracetam', 'Levofloxacin', 'Levothyroxine',
            'Linaclotide', 'Linezolid', 'Lisinopril', 'Lisinopril-HCTZ', 'Loratadine',
            'Losartan', 'Metaxalone', 'Methotrexate', 'Metoclopramide', 'Metoprolol',
            'Metronidazole', 'Minoxidil', 'Mirtazapine', 'Montelukast', 'Mupirocin',
            'Nitrofurantoin', 'Omeprazole', 'Oxcarbazepine', 'Phenytoin', 'Pramipexole',
            'Prednisone', 'Primidone', 'Propranolol', 'Quetiapine', 'Retinoids',
            'Rufinamide', 'Salmeterol', 'Simvastatin', 'Spironolactone', 'Stiripentol',
            'Tenofovir', 'Tiagabine', 'Tiotropium', 'Trimethoprim-Sulfamethoxazole', 'Valacyclovir',
            'Valproic Acid', 'Vancomycin', 'Vardenafil', 'Voriconazole', 'Zonisamide'
        ];

        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
