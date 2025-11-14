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
            // Avanafil
            [
                'name' => 'Avanafil',
                'generic_name' => 'Avanafil',
                'drug_class' => 'Phosphodiesterase Type 5 Inhibitors',
                'description' => 'Used to treat erectile dysfunction.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Avanafil',
                'generic_name' => 'Avanafil',
                'drug_class' => 'Phosphodiesterase Type 5 Inhibitors',
                'description' => 'Used to treat erectile dysfunction.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Avanafil',
                'generic_name' => 'Avanafil',
                'drug_class' => 'Phosphodiesterase Type 5 Inhibitors',
                'description' => 'Used to treat erectile dysfunction.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '200mg',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],

            // Azithromycin
            [
                'name' => 'Azithromycin',
                'generic_name' => 'Azithromycin',
                'drug_class' => 'Macrolide Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
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
                'name' => 'Azithromycin',
                'generic_name' => 'Azithromycin',
                'drug_class' => 'Macrolide Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
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
                'name' => 'Azithromycin',
                'generic_name' => 'Azithromycin',
                'drug_class' => 'Macrolide Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '200mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],

            // Brivaracetam
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 12.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 3
            ],
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '75mg',
                'manufacturer' => 'Various',
                'unit_price' => 18.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Oral',
                'strength' => '10mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 25.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],
            [
                'name' => 'Brivaracetam',
                'generic_name' => 'Brivaracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial-onset seizures.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intravenous',
                'strength' => '50mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 30.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 7
            ],

            // Butalbital-APAP-Caffeine
            [
                'name' => 'Butalbital-Acetaminophen-Caffeine',
                'generic_name' => 'Butalbital-APAP-Caffeine',
                'drug_class' => 'Barbiturate-Analgesic Combinations',
                'description' => 'Used to treat tension headaches and migraines.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '50mg-325mg-40mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => true,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Butalbital-Acetaminophen-Caffeine',
                'generic_name' => 'Butalbital-APAP-Caffeine',
                'drug_class' => 'Barbiturate-Analgesic Combinations',
                'description' => 'Used to treat tension headaches and migraines.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg-325mg-40mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.80,
                'requires_prescription' => true,
                'controlled_substance' => true,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Carbamazepine
            [
                'name' => 'Carbamazepine',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
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
                'name' => 'Carbamazepine',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '200mg',
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
                'name' => 'Carbamazepine',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '400mg',
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
                'name' => 'Carbamazepine ER',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Extended-release formulation used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.25,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Carbamazepine ER',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Extended-release formulation used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '200mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],
            [
                'name' => 'Carbamazepine ER',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Extended-release formulation used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '400mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],
            [
                'name' => 'Carbamazepine',
                'generic_name' => 'Carbamazepine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures, nerve pain, and bipolar disorder.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '100mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 7
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
            'Avanafil',
            'Azithromycin',
            'Brivaracetam',
            'Butalbital-APAP-Caffeine',
            'Carbamazepine'
        ];

        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
