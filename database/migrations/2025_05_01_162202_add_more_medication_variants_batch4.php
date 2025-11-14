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
            // Felbamate
            [
                'name' => 'Felbamate',
                'generic_name' => 'Felbamate',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in patients with Lennox-Gastaut syndrome and partial seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '400mg',
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
                'name' => 'Felbamate',
                'generic_name' => 'Felbamate',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in patients with Lennox-Gastaut syndrome and partial seizures.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '600mg',
                'manufacturer' => 'Various',
                'unit_price' => 4.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Felbamate',
                'generic_name' => 'Felbamate',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in patients with Lennox-Gastaut syndrome and partial seizures.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '600mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 30.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],

            // Fexofenadine
            [
                'name' => 'Fexofenadine',
                'generic_name' => 'Fexofenadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of seasonal allergies such as runny nose, sneezing, and itchy, watery eyes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '30mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Fexofenadine',
                'generic_name' => 'Fexofenadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of seasonal allergies such as runny nose, sneezing, and itchy, watery eyes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '60mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Fexofenadine',
                'generic_name' => 'Fexofenadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of seasonal allergies such as runny nose, sneezing, and itchy, watery eyes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '120mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.50,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Fexofenadine',
                'generic_name' => 'Fexofenadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of seasonal allergies such as runny nose, sneezing, and itchy, watery eyes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '180mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.60,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 4
            ],
            [
                'name' => 'Fexofenadine',
                'generic_name' => 'Fexofenadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of seasonal allergies such as runny nose, sneezing, and itchy, watery eyes.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '30mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],

            // Fluconazole
            [
                'name' => 'Fluconazole',
                'generic_name' => 'Fluconazole',
                'drug_class' => 'Azole Antifungals',
                'description' => 'Used to treat fungal infections, including yeast infections of the vagina, mouth, throat, esophagus, abdomen, lungs, blood, and other organs.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
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
                'name' => 'Fluconazole',
                'generic_name' => 'Fluconazole',
                'drug_class' => 'Azole Antifungals',
                'description' => 'Used to treat fungal infections, including yeast infections of the vagina, mouth, throat, esophagus, abdomen, lungs, blood, and other organs.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Fluconazole',
                'generic_name' => 'Fluconazole',
                'drug_class' => 'Azole Antifungals',
                'description' => 'Used to treat fungal infections, including yeast infections of the vagina, mouth, throat, esophagus, abdomen, lungs, blood, and other organs.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '150mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 3
            ],
            [
                'name' => 'Fluconazole',
                'generic_name' => 'Fluconazole',
                'drug_class' => 'Azole Antifungals',
                'description' => 'Used to treat fungal infections, including yeast infections of the vagina, mouth, throat, esophagus, abdomen, lungs, blood, and other organs.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '200mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Fluconazole',
                'generic_name' => 'Fluconazole',
                'drug_class' => 'Azole Antifungals',
                'description' => 'Used to treat fungal infections, including yeast infections of the vagina, mouth, throat, esophagus, abdomen, lungs, blood, and other organs.',
                'dosage_form' => 'Suspension',
                'route_of_administration' => 'Oral',
                'strength' => '10mg/mL',
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
                'name' => 'Fluconazole',
                'generic_name' => 'Fluconazole',
                'drug_class' => 'Azole Antifungals',
                'description' => 'Used to treat fungal infections, including yeast infections of the vagina, mouth, throat, esophagus, abdomen, lungs, blood, and other organs.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intravenous',
                'strength' => '2mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 30.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],

            // Fluticasone
            [
                'name' => 'Fluticasone',
                'generic_name' => 'Fluticasone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation and irritation caused by conditions such as allergic rhinitis, asthma, and certain skin conditions.',
                'dosage_form' => 'Nasal Spray',
                'route_of_administration' => 'Intranasal',
                'strength' => '50mcg/spray',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Fluticasone',
                'generic_name' => 'Fluticasone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation and irritation caused by conditions such as allergic rhinitis, asthma, and certain skin conditions.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '44mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 50.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Fluticasone',
                'generic_name' => 'Fluticasone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation and irritation caused by conditions such as allergic rhinitis, asthma, and certain skin conditions.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '110mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 60.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Fluticasone',
                'generic_name' => 'Fluticasone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation and irritation caused by conditions such as allergic rhinitis, asthma, and certain skin conditions.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '220mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 70.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 4
            ],
            [
                'name' => 'Fluticasone',
                'generic_name' => 'Fluticasone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation and irritation caused by conditions such as allergic rhinitis, asthma, and certain skin conditions.',
                'dosage_form' => 'Cream',
                'route_of_administration' => 'Topical',
                'strength' => '0.05%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 5
            ],
            [
                'name' => 'Fluticasone',
                'generic_name' => 'Fluticasone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation and irritation caused by conditions such as allergic rhinitis, asthma, and certain skin conditions.',
                'dosage_form' => 'Ointment',
                'route_of_administration' => 'Topical',
                'strength' => '0.005%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 6
            ],

            // Furosemide
            [
                'name' => 'Furosemide',
                'generic_name' => 'Furosemide',
                'drug_class' => 'Loop Diuretics',
                'description' => 'Used to treat fluid retention (edema) in people with congestive heart failure, liver disease, or kidney disorders. It is also used to treat high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '20mg',
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
                'name' => 'Furosemide',
                'generic_name' => 'Furosemide',
                'drug_class' => 'Loop Diuretics',
                'description' => 'Used to treat fluid retention (edema) in people with congestive heart failure, liver disease, or kidney disorders. It is also used to treat high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '40mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            [
                'name' => 'Furosemide',
                'generic_name' => 'Furosemide',
                'drug_class' => 'Loop Diuretics',
                'description' => 'Used to treat fluid retention (edema) in people with congestive heart failure, liver disease, or kidney disorders. It is also used to treat high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '80mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            [
                'name' => 'Furosemide',
                'generic_name' => 'Furosemide',
                'drug_class' => 'Loop Diuretics',
                'description' => 'Used to treat fluid retention (edema) in people with congestive heart failure, liver disease, or kidney disorders. It is also used to treat high blood pressure.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Oral',
                'strength' => '10mg/mL',
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
                'name' => 'Furosemide',
                'generic_name' => 'Furosemide',
                'drug_class' => 'Loop Diuretics',
                'description' => 'Used to treat fluid retention (edema) in people with congestive heart failure, liver disease, or kidney disorders. It is also used to treat high blood pressure.',
                'dosage_form' => 'Injection',
                'route_of_administration' => 'Intravenous/Intramuscular',
                'strength' => '10mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
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
            'Felbamate',
            'Fexofenadine',
            'Fluconazole',
            'Fluticasone',
            'Furosemide'
        ];

        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
