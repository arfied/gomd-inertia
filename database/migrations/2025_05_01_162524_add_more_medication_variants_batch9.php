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
            // Retinoids (Tretinoin)
            [
                'name' => 'Tretinoin',
                'generic_name' => 'Retinoids',
                'drug_class' => 'Retinoids',
                'description' => 'Used to treat acne, sun-damaged skin, and fine wrinkles.',
                'dosage_form' => 'Cream',
                'route_of_administration' => 'Topical',
                'strength' => '0.025%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Tretinoin',
                'generic_name' => 'Retinoids',
                'drug_class' => 'Retinoids',
                'description' => 'Used to treat acne, sun-damaged skin, and fine wrinkles.',
                'dosage_form' => 'Cream',
                'route_of_administration' => 'Topical',
                'strength' => '0.05%',
                'manufacturer' => 'Various',
                'unit_price' => 20.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Rufinamide
            [
                'name' => 'Rufinamide',
                'generic_name' => 'Rufinamide',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures associated with Lennox-Gastaut syndrome.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '200mg',
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
                'name' => 'Rufinamide',
                'generic_name' => 'Rufinamide',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures associated with Lennox-Gastaut syndrome.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '400mg',
                'manufacturer' => 'Various',
                'unit_price' => 8.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            
            // Salmeterol
            [
                'name' => 'Salmeterol',
                'generic_name' => 'Salmeterol',
                'drug_class' => 'Long-Acting Beta Agonists',
                'description' => 'Used to prevent asthma attacks and COPD symptoms.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '25mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 50.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Salmeterol/Fluticasone',
                'generic_name' => 'Salmeterol',
                'drug_class' => 'Long-Acting Beta Agonists/Corticosteroids',
                'description' => 'Combination medication used to prevent asthma attacks and COPD symptoms.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '50mcg/250mcg',
                'manufacturer' => 'Various',
                'unit_price' => 75.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Simvastatin
            [
                'name' => 'Simvastatin',
                'generic_name' => 'Simvastatin',
                'drug_class' => 'HMG-CoA Reductase Inhibitors (Statins)',
                'description' => 'Used to lower cholesterol and reduce the risk of heart disease.',
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
                'name' => 'Simvastatin',
                'generic_name' => 'Simvastatin',
                'drug_class' => 'HMG-CoA Reductase Inhibitors (Statins)',
                'description' => 'Used to lower cholesterol and reduce the risk of heart disease.',
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
            
            // Spironolactone
            [
                'name' => 'Spironolactone',
                'generic_name' => 'Spironolactone',
                'drug_class' => 'Potassium-Sparing Diuretics',
                'description' => 'Used to treat high blood pressure, heart failure, and fluid retention.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.25,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Spironolactone',
                'generic_name' => 'Spironolactone',
                'drug_class' => 'Potassium-Sparing Diuretics',
                'description' => 'Used to treat high blood pressure, heart failure, and fluid retention.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
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
            'Retinoids', 'Rufinamide', 'Salmeterol', 'Simvastatin', 'Spironolactone'
        ];
        
        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
