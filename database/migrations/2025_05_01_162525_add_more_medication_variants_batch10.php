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
            // Stiripentol
            [
                'name' => 'Stiripentol',
                'generic_name' => 'Stiripentol',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat Dravet syndrome, a rare form of epilepsy.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Stiripentol',
                'generic_name' => 'Stiripentol',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat Dravet syndrome, a rare form of epilepsy.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 35.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Tenofovir
            [
                'name' => 'Tenofovir Disoproxil Fumarate',
                'generic_name' => 'Tenofovir',
                'drug_class' => 'Nucleotide Reverse Transcriptase Inhibitors',
                'description' => 'Used to treat HIV infection and chronic hepatitis B.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '300mg',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Tenofovir Alafenamide',
                'generic_name' => 'Tenofovir',
                'drug_class' => 'Nucleotide Reverse Transcriptase Inhibitors',
                'description' => 'Used to treat HIV infection and chronic hepatitis B.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Tiagabine
            [
                'name' => 'Tiagabine',
                'generic_name' => 'Tiagabine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial seizures in epilepsy.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '4mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Tiagabine',
                'generic_name' => 'Tiagabine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial seizures in epilepsy.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '12mg',
                'manufacturer' => 'Various',
                'unit_price' => 3.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Tiotropium
            [
                'name' => 'Tiotropium',
                'generic_name' => 'Tiotropium',
                'drug_class' => 'Anticholinergics',
                'description' => 'Used to treat chronic obstructive pulmonary disease (COPD).',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '18mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 60.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Tiotropium',
                'generic_name' => 'Tiotropium',
                'drug_class' => 'Anticholinergics',
                'description' => 'Used to treat chronic obstructive pulmonary disease (COPD).',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Inhalation',
                'strength' => '2.5mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 65.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Trimethoprim-Sulfamethoxazole
            [
                'name' => 'Trimethoprim-Sulfamethoxazole',
                'generic_name' => 'Trimethoprim-Sulfamethoxazole',
                'drug_class' => 'Sulfonamide Antibiotics',
                'description' => 'Used to treat a variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '80mg-400mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.15,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Trimethoprim-Sulfamethoxazole',
                'generic_name' => 'Trimethoprim-Sulfamethoxazole',
                'drug_class' => 'Sulfonamide Antibiotics',
                'description' => 'Used to treat a variety of bacterial infections.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '40mg-200mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
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
            'Stiripentol', 'Tenofovir', 'Tiagabine', 'Tiotropium', 'Trimethoprim-Sulfamethoxazole'
        ];
        
        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
