<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define drug class mappings based on medication types
        $drugClassMappings = [
            'antidepressants' => 'Antidepressants',
            'anxiolytics' => 'Anxiolytics',
            'muscle-relaxants' => 'Muscle Relaxants',
            'anti-inflammatories' => 'Anti-Inflammatory Drugs',
            'antiandrogens' => 'Antiandrogens',
            'cardiovascular' => 'Cardiovascular Drugs',
            'anticonvulsants' => 'Anticonvulsants',
            'antidiabetics' => 'Antidiabetic Drugs',
            'antibiotics' => 'Antibiotics',
            'antivirals' => 'Antivirals',
            'dermatologicals' => 'Dermatological Drugs',
            'Serotonin Antagonist and Reuptake Inhibitor (SARI)' => 'Serotonin Antagonist and Reuptake Inhibitors'
        ];

        // Update specific medications based on generic name
        $specificMappings = [
            'Metformin' => 'Biguanides',
            'Sildenafil' => 'Phosphodiesterase Type 5 Inhibitors',
            'Tadalafil' => 'Phosphodiesterase Type 5 Inhibitors',
            'Escitalopram' => 'Selective Serotonin Reuptake Inhibitors (SSRIs)',
            'Fluoxetine' => 'Selective Serotonin Reuptake Inhibitors (SSRIs)',
            'Sertraline' => 'Selective Serotonin Reuptake Inhibitors (SSRIs)',
            'Paroxetine' => 'Selective Serotonin Reuptake Inhibitors (SSRIs)',
            'Duloxetine' => 'Serotonin-Norepinephrine Reuptake Inhibitors (SNRIs)',
            'Venlafaxine' => 'Serotonin-Norepinephrine Reuptake Inhibitors (SNRIs)',
            'Bupropion' => 'Norepinephrine-Dopamine Reuptake Inhibitors (NDRIs)',
            'Amitriptyline' => 'Tricyclic Antidepressants (TCAs)',
            'Nortriptyline' => 'Tricyclic Antidepressants (TCAs)',
            'Trazodone' => 'Serotonin Antagonist and Reuptake Inhibitors (SARIs)',
            'Baclofen' => 'GABA-B Receptor Agonists',
            'Cyclobenzaprine' => 'Centrally Acting Muscle Relaxants',
            'Methocarbamol' => 'Centrally Acting Muscle Relaxants',
            'Tizanidine' => 'Alpha-2 Adrenergic Agonists',
            'Orphenadrine' => 'Anticholinergic Muscle Relaxants',
            'Celecoxib' => 'COX-2 Selective NSAIDs',
            'Meloxicam' => 'NSAIDs',
            'Etodolac' => 'NSAIDs',
            'Nabumetone' => 'NSAIDs',
            'Finasteride' => '5-Alpha Reductase Inhibitors',
            'Acyclovir' => 'Nucleoside Analogues',
            'Topiramate' => 'Anticonvulsants',
            'Buspirone' => 'Azapirones',
            'Tretinoin' => 'Retinoids',
            'Adapalene' => 'Retinoids',
            'Hydroquinone' => 'Skin Lightening Agents',
            'Azelaic Acid' => 'Dicarboxylic Acids',
            'Rifaximin' => 'Rifamycin Antibiotics',
            'Albuterol' => 'Short-Acting Beta-2 Agonists'
        ];

        // Update drug_class based on type
        foreach ($drugClassMappings as $type => $drugClass) {
            DB::table('medications')
                ->where('type', $type)
                ->whereNull('drug_class')
                ->update(['drug_class' => $drugClass]);
        }

        // Update drug_class based on generic_name
        foreach ($specificMappings as $genericName => $drugClass) {
            DB::table('medications')
                ->where('generic_name', 'like', '%' . $genericName . '%')
                ->whereNull('drug_class')
                ->update(['drug_class' => $drugClass]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set drug_class back to null for all medications
        DB::table('medications')
            ->update(['drug_class' => null]);
    }
};
