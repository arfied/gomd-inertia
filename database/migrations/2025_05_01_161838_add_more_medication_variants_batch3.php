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
            // Doxycycline
            [
                'name' => 'Doxycycline',
                'generic_name' => 'Doxycycline',
                'drug_class' => 'Tetracycline Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections, including acne, and to prevent malaria.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
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
                'name' => 'Doxycycline',
                'generic_name' => 'Doxycycline',
                'drug_class' => 'Tetracycline Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections, including acne, and to prevent malaria.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
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
                'name' => 'Doxycycline',
                'generic_name' => 'Doxycycline',
                'drug_class' => 'Tetracycline Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections, including acne, and to prevent malaria.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '75mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.60,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Doxycycline',
                'generic_name' => 'Doxycycline',
                'drug_class' => 'Tetracycline Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections, including acne, and to prevent malaria.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.80,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Doxycycline',
                'generic_name' => 'Doxycycline',
                'drug_class' => 'Tetracycline Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections, including acne, and to prevent malaria.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '150mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],
            [
                'name' => 'Doxycycline',
                'generic_name' => 'Doxycycline',
                'drug_class' => 'Tetracycline Antibiotics',
                'description' => 'Used to treat a wide variety of bacterial infections, including acne, and to prevent malaria.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '25mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],

            // Dutasteride
            [
                'name' => 'Dutasteride',
                'generic_name' => 'Dutasteride',
                'drug_class' => '5-Alpha Reductase Inhibitors',
                'description' => 'Used to treat benign prostatic hyperplasia (enlarged prostate) and male pattern baldness.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '0.5mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],

            // Epinephrine
            [
                'name' => 'Epinephrine',
                'generic_name' => 'Epinephrine',
                'drug_class' => 'Adrenergic Agonists',
                'description' => 'Used to treat severe allergic reactions (anaphylaxis), cardiac arrest, and asthma attacks.',
                'dosage_form' => 'Auto-Injector',
                'route_of_administration' => 'Intramuscular',
                'strength' => '0.15mg/0.15mL',
                'manufacturer' => 'Various',
                'unit_price' => 100.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Epinephrine',
                'generic_name' => 'Epinephrine',
                'drug_class' => 'Adrenergic Agonists',
                'description' => 'Used to treat severe allergic reactions (anaphylaxis), cardiac arrest, and asthma attacks.',
                'dosage_form' => 'Auto-Injector',
                'route_of_administration' => 'Intramuscular',
                'strength' => '0.3mg/0.3mL',
                'manufacturer' => 'Various',
                'unit_price' => 100.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Epinephrine',
                'generic_name' => 'Epinephrine',
                'drug_class' => 'Adrenergic Agonists',
                'description' => 'Used to treat severe allergic reactions (anaphylaxis), cardiac arrest, and asthma attacks.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intravenous/Intramuscular/Subcutaneous',
                'strength' => '1mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Epinephrine',
                'generic_name' => 'Epinephrine',
                'drug_class' => 'Adrenergic Agonists',
                'description' => 'Used to treat severe allergic reactions (anaphylaxis), cardiac arrest, and asthma attacks.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Inhalation',
                'strength' => '2.25%',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],

            // Estradiol
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '0.5mg',
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
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
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
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2mg',
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
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Patch',
                'route_of_administration' => 'Transdermal',
                'strength' => '0.025mg/24hr',
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
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Patch',
                'route_of_administration' => 'Transdermal',
                'strength' => '0.05mg/24hr',
                'manufacturer' => 'Various',
                'unit_price' => 12.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Patch',
                'route_of_administration' => 'Transdermal',
                'strength' => '0.075mg/24hr',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Patch',
                'route_of_administration' => 'Transdermal',
                'strength' => '0.1mg/24hr',
                'manufacturer' => 'Various',
                'unit_price' => 18.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 7
            ],
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Gel',
                'route_of_administration' => 'Topical',
                'strength' => '0.06%',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 8
            ],
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Ring',
                'route_of_administration' => 'Vaginal',
                'strength' => '2mg',
                'manufacturer' => 'Various',
                'unit_price' => 100.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 9
            ],
            [
                'name' => 'Estradiol',
                'generic_name' => 'Estradiol',
                'drug_class' => 'Estrogens',
                'description' => 'Used to treat symptoms of menopause, prevent osteoporosis, and treat hormone deficiencies.',
                'dosage_form' => 'Cream',
                'route_of_administration' => 'Vaginal',
                'strength' => '0.01%',
                'manufacturer' => 'Various',
                'unit_price' => 30.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 10
            ],

            // Ethosuximide
            [
                'name' => 'Ethosuximide',
                'generic_name' => 'Ethosuximide',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat absence seizures (petit mal seizures).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Ethosuximide',
                'generic_name' => 'Ethosuximide',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat absence seizures (petit mal seizures).',
                'dosage_form' => 'Syrup',
                'route_of_administration' => 'Oral',
                'strength' => '250mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
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
        $genericNames = [
            'Doxycycline',
            'Dutasteride',
            'Epinephrine',
            'Estradiol',
            'Ethosuximide'
        ];

        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
