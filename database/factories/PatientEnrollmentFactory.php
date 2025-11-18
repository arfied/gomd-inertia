<?php

namespace Database\Factories;

use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatientEnrollmentFactory extends Factory
{
    protected $model = PatientEnrollment::class;

    public function definition(): array
    {
        return [
            'patient_uuid' => Str::uuid(),
            'user_id' => User::factory(),
            'source' => 'test',
            'metadata' => [],
            'enrolled_at' => now(),
        ];
    }
}

