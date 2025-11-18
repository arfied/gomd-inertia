<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\MedicationSearchIndex;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationSearchIndexFactory extends Factory
{
    protected $model = MedicationSearchIndex::class;

    public function definition(): array
    {
        return [
            'medication_id' => Medication::factory(),
            'name' => $this->faker->word(),
            'generic_name' => $this->faker->word(),
            'drug_class' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['prescription', 'otc']),
            'status' => 'active',
            'unit_price' => $this->faker->randomFloat(2, 1, 100),
            'requires_prescription' => $this->faker->boolean(),
            'controlled_substance' => $this->faker->boolean(),
        ];
    }
}

