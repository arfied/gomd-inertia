<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'generic_name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['prescription', 'otc']),
            'drug_class' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'dosage_form' => $this->faker->randomElement(['tablet', 'capsule', 'liquid']),
            'strength' => $this->faker->numerify('### mg'),
            'manufacturer' => $this->faker->company(),
            'ndc_number' => $this->faker->numerify('##########'),
            'unit_price' => $this->faker->randomFloat(2, 1, 100),
            'requires_prescription' => $this->faker->boolean(),
            'controlled_substance' => $this->faker->boolean(),
            'status' => 'active',
        ];
    }
}

