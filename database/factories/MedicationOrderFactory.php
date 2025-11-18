<?php

namespace Database\Factories;

use App\Models\MedicationOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationOrderFactory extends Factory
{
    protected $model = MedicationOrder::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'doctor_id' => null,
            'prescription_id' => null,
            'status' => MedicationOrder::STATUS_PENDING,
            'patient_notes' => $this->faker->sentence(),
            'doctor_notes' => null,
            'rejection_reason' => null,
            'assigned_at' => null,
            'prescribed_at' => null,
            'completed_at' => null,
            'rejected_at' => null,
        ];
    }
}

