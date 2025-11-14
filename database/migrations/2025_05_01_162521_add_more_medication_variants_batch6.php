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
            // Metoprolol
            [
                'name' => 'Metoprolol Tartrate',
                'generic_name' => 'Metoprolol',
                'drug_class' => 'Beta Blockers',
                'description' => 'Used to treat high blood pressure, chest pain (angina), and heart failure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.20,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 1
            ],
            [
                'name' => 'Metoprolol Tartrate',
                'generic_name' => 'Metoprolol',
                'drug_class' => 'Beta Blockers',
                'description' => 'Used to treat high blood pressure, chest pain (angina), and heart failure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.25,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Metoprolol Succinate',
                'generic_name' => 'Metoprolol',
                'drug_class' => 'Beta Blockers',
                'description' => 'Extended-release form used to treat high blood pressure, chest pain (angina), and heart failure.',
                'dosage_form' => 'Tablet (Extended Release)',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            
            // Metronidazole
            [
                'name' => 'Metronidazole',
                'generic_name' => 'Metronidazole',
                'drug_class' => 'Nitroimidazole Antibiotics',
                'description' => 'Used to treat bacterial and parasitic infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '250mg',
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
                'name' => 'Metronidazole',
                'generic_name' => 'Metronidazole',
                'drug_class' => 'Nitroimidazole Antibiotics',
                'description' => 'Used to treat bacterial and parasitic infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],
            [
                'name' => 'Metronidazole',
                'generic_name' => 'Metronidazole',
                'drug_class' => 'Nitroimidazole Antibiotics',
                'description' => 'Used to treat bacterial and parasitic infections.',
                'dosage_form' => 'Gel',
                'route_of_administration' => 'Topical',
                'strength' => '0.75%',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 3
            ],
            
            // Minoxidil
            [
                'name' => 'Minoxidil',
                'generic_name' => 'Minoxidil',
                'drug_class' => 'Vasodilators',
                'description' => 'Used to treat high blood pressure and hair loss.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
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
                'name' => 'Minoxidil',
                'generic_name' => 'Minoxidil',
                'drug_class' => 'Vasodilators',
                'description' => 'Used to treat high blood pressure and hair loss.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Topical',
                'strength' => '5%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Mirtazapine
            [
                'name' => 'Mirtazapine',
                'generic_name' => 'Mirtazapine',
                'drug_class' => 'Tetracyclic Antidepressants',
                'description' => 'Used to treat depression and anxiety disorders.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '15mg',
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
                'name' => 'Mirtazapine',
                'generic_name' => 'Mirtazapine',
                'drug_class' => 'Tetracyclic Antidepressants',
                'description' => 'Used to treat depression and anxiety disorders.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '30mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.60,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],
            
            // Montelukast
            [
                'name' => 'Montelukast',
                'generic_name' => 'Montelukast',
                'drug_class' => 'Leukotriene Receptor Antagonists',
                'description' => 'Used to prevent and treat asthma and allergic rhinitis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
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
                'name' => 'Montelukast',
                'generic_name' => 'Montelukast',
                'drug_class' => 'Leukotriene Receptor Antagonists',
                'description' => 'Used to prevent and treat asthma and allergic rhinitis.',
                'dosage_form' => 'Chewable Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.90,
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
            'Metoprolol', 'Metronidazole', 'Minoxidil', 'Mirtazapine', 'Montelukast'
        ];
        
        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
