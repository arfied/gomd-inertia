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
            // Valacyclovir
            [
                'name' => 'Valacyclovir',
                'generic_name' => 'Valacyclovir',
                'drug_class' => 'Antiviral Agents',
                'description' => 'Used to treat herpes virus infections, including shingles, cold sores, and genital herpes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
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
                'name' => 'Valacyclovir',
                'generic_name' => 'Valacyclovir',
                'drug_class' => 'Antiviral Agents',
                'description' => 'Used to treat herpes virus infections, including shingles, cold sores, and genital herpes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '1g',
                'manufacturer' => 'Various',
                'unit_price' => 2.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Valproic Acid
            [
                'name' => 'Valproic Acid',
                'generic_name' => 'Valproic Acid',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures, bipolar disorder, and prevent migraines.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Divalproex Sodium',
                'generic_name' => 'Valproic Acid',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures, bipolar disorder, and prevent migraines.',
                'dosage_form' => 'Tablet (Extended Release)',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.75,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Vancomycin
            [
                'name' => 'Vancomycin',
                'generic_name' => 'Vancomycin',
                'drug_class' => 'Glycopeptide Antibiotics',
                'description' => 'Used to treat serious bacterial infections, particularly those caused by methicillin-resistant Staphylococcus aureus (MRSA).',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '125mg',
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
                'name' => 'Vancomycin',
                'generic_name' => 'Vancomycin',
                'drug_class' => 'Glycopeptide Antibiotics',
                'description' => 'Used to treat serious bacterial infections, particularly those caused by methicillin-resistant Staphylococcus aureus (MRSA).',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Intravenous',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Vardenafil
            [
                'name' => 'Vardenafil',
                'generic_name' => 'Vardenafil',
                'drug_class' => 'Phosphodiesterase Type 5 Inhibitors',
                'description' => 'Used to treat erectile dysfunction.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Vardenafil',
                'generic_name' => 'Vardenafil',
                'drug_class' => 'Phosphodiesterase Type 5 Inhibitors',
                'description' => 'Used to treat erectile dysfunction.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '20mg',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Voriconazole
            [
                'name' => 'Voriconazole',
                'generic_name' => 'Voriconazole',
                'drug_class' => 'Antifungals',
                'description' => 'Used to treat serious fungal infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '200mg',
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
                'name' => 'Voriconazole',
                'generic_name' => 'Voriconazole',
                'drug_class' => 'Antifungals',
                'description' => 'Used to treat serious fungal infections.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Intravenous',
                'strength' => '200mg',
                'manufacturer' => 'Various',
                'unit_price' => 50.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Zonisamide
            [
                'name' => 'Zonisamide',
                'generic_name' => 'Zonisamide',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial seizures in adults with epilepsy.',
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
                'order' => 1
            ],
            [
                'name' => 'Zonisamide',
                'generic_name' => 'Zonisamide',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat partial seizures in adults with epilepsy.',
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
            'Valacyclovir', 'Valproic Acid', 'Vancomycin', 'Vardenafil', 'Voriconazole', 'Zonisamide'
        ];
        
        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
