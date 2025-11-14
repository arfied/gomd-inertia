<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Medication;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $medications = [
            // Pramipexole
            [
                'name' => 'Pramipexole',
                'generic_name' => 'Pramipexole',
                'drug_class' => 'Dopamine Agonists',
                'description' => 'Used to treat Parkinson\'s disease and restless legs syndrome.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '0.25mg',
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
                'name' => 'Pramipexole',
                'generic_name' => 'Pramipexole',
                'drug_class' => 'Dopamine Agonists',
                'description' => 'Used to treat Parkinson\'s disease and restless legs syndrome.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '1mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.75,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Prednisone
            [
                'name' => 'Prednisone',
                'generic_name' => 'Prednisone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation, allergic reactions, and autoimmune disorders.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.20,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Prednisone',
                'generic_name' => 'Prednisone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation, allergic reactions, and autoimmune disorders.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.25,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Primidone
            [
                'name' => 'Primidone',
                'generic_name' => 'Primidone',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to control seizures in epilepsy and essential tremor.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Primidone',
                'generic_name' => 'Primidone',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to control seizures in epilepsy and essential tremor.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.60,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Propranolol
            [
                'name' => 'Propranolol',
                'generic_name' => 'Propranolol',
                'drug_class' => 'Beta Blockers',
                'description' => 'Used to treat high blood pressure, angina, and certain types of tremors and anxiety.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.15,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Propranolol',
                'generic_name' => 'Propranolol',
                'drug_class' => 'Beta Blockers',
                'description' => 'Used to treat high blood pressure, angina, and certain types of tremors and anxiety.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '40mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.20,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Quetiapine
            [
                'name' => 'Quetiapine',
                'generic_name' => 'Quetiapine',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and major depressive disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Quetiapine',
                'generic_name' => 'Quetiapine',
                'drug_class' => 'Atypical Antipsychotics',
                'description' => 'Used to treat schizophrenia, bipolar disorder, and major depressive disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ]
        ];

        foreach ($medications as $medication) {
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
            'Pramipexole', 'Prednisone', 'Primidone', 'Propranolol', 'Quetiapine'
        ];
        
        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
