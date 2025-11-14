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
            // Mupirocin
            [
                'name' => 'Mupirocin',
                'generic_name' => 'Mupirocin',
                'drug_class' => 'Topical Antibiotics',
                'description' => 'Used to treat bacterial skin infections such as impetigo.',
                'dosage_form' => 'Ointment',
                'route_of_administration' => 'Topical',
                'strength' => '2%',
                'manufacturer' => 'Various',
                'unit_price' => 8.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Mupirocin',
                'generic_name' => 'Mupirocin',
                'drug_class' => 'Topical Antibiotics',
                'description' => 'Used to treat bacterial skin infections such as impetigo.',
                'dosage_form' => 'Cream',
                'route_of_administration' => 'Topical',
                'strength' => '2%',
                'manufacturer' => 'Various',
                'unit_price' => 9.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Nitrofurantoin
            [
                'name' => 'Nitrofurantoin',
                'generic_name' => 'Nitrofurantoin',
                'drug_class' => 'Urinary Tract Antibiotics',
                'description' => 'Used to treat urinary tract infections.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.60,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Nitrofurantoin',
                'generic_name' => 'Nitrofurantoin',
                'drug_class' => 'Urinary Tract Antibiotics',
                'description' => 'Used to treat urinary tract infections.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '25mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Omeprazole
            [
                'name' => 'Omeprazole',
                'generic_name' => 'Omeprazole',
                'drug_class' => 'Proton Pump Inhibitors',
                'description' => 'Used to treat gastroesophageal reflux disease (GERD), peptic ulcer disease, and other conditions caused by excess stomach acid.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '20mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.25,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Omeprazole',
                'generic_name' => 'Omeprazole',
                'drug_class' => 'Proton Pump Inhibitors',
                'description' => 'Used to treat gastroesophageal reflux disease (GERD), peptic ulcer disease, and other conditions caused by excess stomach acid.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '40mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.35,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Oxcarbazepine
            [
                'name' => 'Oxcarbazepine',
                'generic_name' => 'Oxcarbazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in epilepsy and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '300mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.80,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Oxcarbazepine',
                'generic_name' => 'Oxcarbazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in epilepsy and bipolar disorder.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '300mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Phenytoin
            [
                'name' => 'Phenytoin',
                'generic_name' => 'Phenytoin',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to control seizures in epilepsy.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Phenytoin',
                'generic_name' => 'Phenytoin',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to control seizures in epilepsy.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Injection',
                'strength' => '50mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
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
            'Mupirocin', 'Nitrofurantoin', 'Omeprazole', 'Oxcarbazepine', 'Phenytoin'
        ];
        
        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
