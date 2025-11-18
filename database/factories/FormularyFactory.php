<?php

namespace Database\Factories;

use App\Models\Formulary;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormularyFactory extends Factory
{
    protected $model = Formulary::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->company() . ' Formulary',
            'description' => $this->faker->sentence(),
            'organization_id' => $this->faker->uuid(),
            'type' => $this->faker->randomElement(['insurance', 'hospital', 'clinical_protocol']),
            'status' => 'active',
        ];
    }
}

