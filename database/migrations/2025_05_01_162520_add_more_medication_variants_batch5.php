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
            // Guanfacine
            [
                'name' => 'Guanfacine',
                'generic_name' => 'Guanfacine',
                'drug_class' => 'Alpha-2 Adrenergic Agonists',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD) and high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '1mg',
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
                'name' => 'Guanfacine',
                'generic_name' => 'Guanfacine',
                'drug_class' => 'Alpha-2 Adrenergic Agonists',
                'description' => 'Used to treat attention deficit hyperactivity disorder (ADHD) and high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2mg',
                'manufacturer' => 'Various',
                'unit_price' => 2.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Hydrochlorothiazide
            [
                'name' => 'Hydrochlorothiazide',
                'generic_name' => 'Hydrochlorothiazide',
                'drug_class' => 'Thiazide Diuretics',
                'description' => 'Used to treat high blood pressure and fluid retention.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '12.5mg',
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
                'name' => 'Hydrochlorothiazide',
                'generic_name' => 'Hydrochlorothiazide',
                'drug_class' => 'Thiazide Diuretics',
                'description' => 'Used to treat high blood pressure and fluid retention.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Hydrocortisone
            [
                'name' => 'Hydrocortisone',
                'generic_name' => 'Hydrocortisone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation, allergic reactions, and autoimmune disorders.',
                'dosage_form' => 'Cream',
                'route_of_administration' => 'Topical',
                'strength' => '1%',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Hydrocortisone',
                'generic_name' => 'Hydrocortisone',
                'drug_class' => 'Corticosteroids',
                'description' => 'Used to treat inflammation, allergic reactions, and autoimmune disorders.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Ipratropium
            [
                'name' => 'Ipratropium',
                'generic_name' => 'Ipratropium',
                'drug_class' => 'Anticholinergics',
                'description' => 'Used to treat chronic obstructive pulmonary disease (COPD) and asthma.',
                'dosage_form' => 'Inhaler',
                'route_of_administration' => 'Inhalation',
                'strength' => '17mcg/actuation',
                'manufacturer' => 'Various',
                'unit_price' => 25.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Ipratropium',
                'generic_name' => 'Ipratropium',
                'drug_class' => 'Anticholinergics',
                'description' => 'Used to treat chronic obstructive pulmonary disease (COPD) and asthma.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Inhalation',
                'strength' => '0.02%',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Isosorbide
            [
                'name' => 'Isosorbide Mononitrate',
                'generic_name' => 'Isosorbide',
                'drug_class' => 'Nitrates',
                'description' => 'Used to prevent angina (chest pain) in patients with coronary artery disease.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '20mg',
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
                'name' => 'Isosorbide Dinitrate',
                'generic_name' => 'Isosorbide',
                'drug_class' => 'Nitrates',
                'description' => 'Used to prevent angina (chest pain) in patients with coronary artery disease.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.80,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Ketoconazole
            [
                'name' => 'Ketoconazole',
                'generic_name' => 'Ketoconazole',
                'drug_class' => 'Antifungals',
                'description' => 'Used to treat fungal infections of the skin and hair.',
                'dosage_form' => 'Cream',
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
                'name' => 'Ketoconazole',
                'generic_name' => 'Ketoconazole',
                'drug_class' => 'Antifungals',
                'description' => 'Used to treat fungal infections of the skin and hair.',
                'dosage_form' => 'Shampoo',
                'route_of_administration' => 'Topical',
                'strength' => '1%',
                'manufacturer' => 'Various',
                'unit_price' => 10.00,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Lamotrigine
            [
                'name' => 'Lamotrigine',
                'generic_name' => 'Lamotrigine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
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
                'name' => 'Lamotrigine',
                'generic_name' => 'Lamotrigine',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures and bipolar disorder.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.75,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Levetiracetam
            [
                'name' => 'Levetiracetam',
                'generic_name' => 'Levetiracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in epilepsy.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
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
                'name' => 'Levetiracetam',
                'generic_name' => 'Levetiracetam',
                'drug_class' => 'Anticonvulsants',
                'description' => 'Used to treat seizures in epilepsy.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '1000mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.20,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Levofloxacin
            [
                'name' => 'Levofloxacin',
                'generic_name' => 'Levofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat bacterial infections of the respiratory tract, urinary tract, and skin.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '500mg',
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
                'name' => 'Levofloxacin',
                'generic_name' => 'Levofloxacin',
                'drug_class' => 'Fluoroquinolone Antibiotics',
                'description' => 'Used to treat bacterial infections of the respiratory tract, urinary tract, and skin.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Intravenous',
                'strength' => '25mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Levothyroxine
            [
                'name' => 'Levothyroxine',
                'generic_name' => 'Levothyroxine',
                'drug_class' => 'Thyroid Hormones',
                'description' => 'Used to treat hypothyroidism (low thyroid hormone).',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mcg',
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
                'name' => 'Levothyroxine',
                'generic_name' => 'Levothyroxine',
                'drug_class' => 'Thyroid Hormones',
                'description' => 'Used to treat hypothyroidism (low thyroid hormone).',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '100mcg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Linaclotide
            [
                'name' => 'Linaclotide',
                'generic_name' => 'Linaclotide',
                'drug_class' => 'Guanylate Cyclase-C Agonists',
                'description' => 'Used to treat irritable bowel syndrome with constipation (IBS-C) and chronic idiopathic constipation.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '145mcg',
                'manufacturer' => 'Various',
                'unit_price' => 12.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Linaclotide',
                'generic_name' => 'Linaclotide',
                'drug_class' => 'Guanylate Cyclase-C Agonists',
                'description' => 'Used to treat irritable bowel syndrome with constipation (IBS-C) and chronic idiopathic constipation.',
                'dosage_form' => 'Capsule',
                'route_of_administration' => 'Oral',
                'strength' => '290mcg',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Linezolid
            [
                'name' => 'Linezolid',
                'generic_name' => 'Linezolid',
                'drug_class' => 'Oxazolidinone Antibiotics',
                'description' => 'Used to treat serious bacterial infections, including pneumonia and skin infections.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '600mg',
                'manufacturer' => 'Various',
                'unit_price' => 25.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 1
            ],
            [
                'name' => 'Linezolid',
                'generic_name' => 'Linezolid',
                'drug_class' => 'Oxazolidinone Antibiotics',
                'description' => 'Used to treat serious bacterial infections, including pneumonia and skin infections.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Intravenous',
                'strength' => '2mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 50.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Lisinopril
            [
                'name' => 'Lisinopril',
                'generic_name' => 'Lisinopril',
                'drug_class' => 'Angiotensin-Converting Enzyme (ACE) Inhibitors',
                'description' => 'Used to treat high blood pressure, heart failure, and to improve survival after a heart attack.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
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
                'name' => 'Lisinopril',
                'generic_name' => 'Lisinopril',
                'drug_class' => 'Angiotensin-Converting Enzyme (ACE) Inhibitors',
                'description' => 'Used to treat high blood pressure, heart failure, and to improve survival after a heart attack.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '20mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.30,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Lisinopril-HCTZ
            [
                'name' => 'Lisinopril-Hydrochlorothiazide',
                'generic_name' => 'Lisinopril-HCTZ',
                'drug_class' => 'ACE Inhibitor/Thiazide Diuretic Combinations',
                'description' => 'Used to treat high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg-12.5mg',
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
                'name' => 'Lisinopril-Hydrochlorothiazide',
                'generic_name' => 'Lisinopril-HCTZ',
                'drug_class' => 'ACE Inhibitor/Thiazide Diuretic Combinations',
                'description' => 'Used to treat high blood pressure.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '20mg-12.5mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.60,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Loratadine
            [
                'name' => 'Loratadine',
                'generic_name' => 'Loratadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of allergies, such as sneezing, runny nose, and itchy, watery eyes.',
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
                'order' => 1
            ],
            [
                'name' => 'Loratadine',
                'generic_name' => 'Loratadine',
                'drug_class' => 'Antihistamines',
                'description' => 'Used to relieve symptoms of allergies, such as sneezing, runny nose, and itchy, watery eyes.',
                'dosage_form' => 'Syrup',
                'route_of_administration' => 'Oral',
                'strength' => '5mg/5mL',
                'manufacturer' => 'Various',
                'unit_price' => 5.00,
                'requires_prescription' => false,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Losartan
            [
                'name' => 'Losartan',
                'generic_name' => 'Losartan',
                'drug_class' => 'Angiotensin II Receptor Blockers (ARBs)',
                'description' => 'Used to treat high blood pressure and to protect the kidneys from damage due to diabetes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '25mg',
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
                'name' => 'Losartan',
                'generic_name' => 'Losartan',
                'drug_class' => 'Angiotensin II Receptor Blockers (ARBs)',
                'description' => 'Used to treat high blood pressure and to protect the kidneys from damage due to diabetes.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '50mg',
                'manufacturer' => 'Various',
                'unit_price' => 0.40,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => true,
                'order' => 2
            ],

            // Metaxalone
            [
                'name' => 'Metaxalone',
                'generic_name' => 'Metaxalone',
                'drug_class' => 'Muscle Relaxants',
                'description' => 'Used to relieve pain and stiffness from muscle injuries.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '800mg',
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
                'name' => 'Metaxalone',
                'generic_name' => 'Metaxalone',
                'drug_class' => 'Muscle Relaxants',
                'description' => 'Used to relieve pain and stiffness from muscle injuries.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '400mg',
                'manufacturer' => 'Various',
                'unit_price' => 1.50,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Methotrexate
            [
                'name' => 'Methotrexate',
                'generic_name' => 'Methotrexate',
                'drug_class' => 'Antimetabolites',
                'description' => 'Used to treat certain types of cancer, severe psoriasis, and rheumatoid arthritis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '2.5mg',
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
                'name' => 'Methotrexate',
                'generic_name' => 'Methotrexate',
                'drug_class' => 'Antimetabolites',
                'description' => 'Used to treat certain types of cancer, severe psoriasis, and rheumatoid arthritis.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Injection',
                'strength' => '25mg/mL',
                'manufacturer' => 'Various',
                'unit_price' => 15.00,
                'requires_prescription' => true,
                'controlled_substance' => false,
                'storage_conditions' => 'Store at room temperature away from light and moisture.',
                'status' => 'approved',
                'is_usual_dosage' => false,
                'order' => 2
            ],

            // Metoclopramide
            [
                'name' => 'Metoclopramide',
                'generic_name' => 'Metoclopramide',
                'drug_class' => 'Prokinetic Agents',
                'description' => 'Used to treat heartburn, gastroesophageal reflux disease (GERD), and diabetic gastroparesis.',
                'dosage_form' => 'Tablet',
                'route_of_administration' => 'Oral',
                'strength' => '10mg',
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
                'name' => 'Metoclopramide',
                'generic_name' => 'Metoclopramide',
                'drug_class' => 'Prokinetic Agents',
                'description' => 'Used to treat heartburn, gastroesophageal reflux disease (GERD), and diabetic gastroparesis.',
                'dosage_form' => 'Solution',
                'route_of_administration' => 'Injection',
                'strength' => '5mg/mL',
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
            'Guanfacine', 'Hydrochlorothiazide', 'Hydrocortisone', 'Ipratropium', 'Isosorbide',
            'Ketoconazole', 'Lamotrigine', 'Levetiracetam', 'Levofloxacin', 'Levothyroxine',
            'Linaclotide', 'Linezolid', 'Lisinopril', 'Lisinopril-HCTZ', 'Loratadine',
            'Losartan', 'Metaxalone', 'Methotrexate', 'Metoclopramide'
        ];

        foreach ($genericNames as $genericName) {
            Medication::where('generic_name', $genericName)->delete();
        }
    }
};
