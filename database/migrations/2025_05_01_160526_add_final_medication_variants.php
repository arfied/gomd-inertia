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
        $medications = [
            // Carvedilol
            [
                'name' => 'Carvedilol',
                'generic_name' => 'Carvedilol',
                'drug_class' => 'Alpha/Beta Blockers',
                'description' => 'Used to treat high blood pressure and heart failure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '3.125mg',
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
                'name' => 'Carvedilol',
                'generic_name' => 'Carvedilol',
                'drug_class' => 'Alpha/Beta Blockers',
                'description' => 'Used to treat high blood pressure and heart failure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '6.25mg',
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
                'name' => 'Carvedilol',
                'generic_name' => 'Carvedilol',
                'drug_class' => 'Alpha/Beta Blockers',
                'description' => 'Used to treat high blood pressure and heart failure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '12.5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.35,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Carvedilol',
                'generic_name' => 'Carvedilol',
                'drug_class' => 'Alpha/Beta Blockers',
                'description' => 'Used to treat high blood pressure and heart failure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],

            // Ceftriaxone
            [
                'name' => 'Ceftriaxone',
                'generic_name' => 'Ceftriaxone',
                'drug_class' => 'Cephalosporin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intramuscular/Intravenous',
                'strength' => '250mg',
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
                'name' => 'Ceftriaxone',
                'generic_name' => 'Ceftriaxone',
                'drug_class' => 'Cephalosporin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intramuscular/Intravenous',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 7.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Ceftriaxone',
                'generic_name' => 'Ceftriaxone',
                'drug_class' => 'Cephalosporin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intramuscular/Intravenous',
                'strength' => '1g',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 3
            ],
            [
                'name' => 'Ceftriaxone',
                'generic_name' => 'Ceftriaxone',
                'drug_class' => 'Cephalosporin Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intramuscular/Intravenous',
                'strength' => '2g',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],

            // Cetirizine
            [
                'name' => 'Cetirizine',
                'generic_name' => 'Cetirizine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to treat allergy symptoms such as sneezing, itching, watery eyes, and runny nose.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.15,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Cetirizine',
                'generic_name' => 'Cetirizine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to treat allergy symptoms such as sneezing, itching, watery eyes, and runny nose.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.20,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Cetirizine',
                'generic_name' => 'Cetirizine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to treat allergy symptoms such as sneezing, itching, watery eyes, and runny nose.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Oral',
                'strength' => '5mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],

            // Chlorzoxazone
            [
                'name' => 'Chlorzoxazone',
                'generic_name' => 'Chlorzoxazone',
                'drug_class' => 'Centrally Acting Muscle Relaxants',
                'description' => 'Used to treat muscle pain and stiffness.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
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
                'name' => 'Chlorzoxazone',
                'generic_name' => 'Chlorzoxazone',
                'drug_class' => 'Centrally Acting Muscle Relaxants',
                'description' => 'Used to treat muscle pain and stiffness.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.75,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],

            // Ciprofloxacin
            [
                'name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
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
                'name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
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
                'name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '750mg',
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
                'name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Oral',
                'strength' => '250mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Ophthalmic',
                'strength' => '0.3%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
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
        $genericNames = [
            'Carvedilol',
            'Ceftriaxone',
            'Cetirizine',
            'Chlorzoxazone',
            'Ciprofloxacin'
        ];

        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
