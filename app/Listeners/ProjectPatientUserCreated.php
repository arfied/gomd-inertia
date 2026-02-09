<?php

namespace App\Listeners;

use App\Domain\Signup\Events\PatientUserCreated;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Event listener that creates a patient user in the database
 * when a PatientUserCreated event is dispatched.
 *
 * Also updates the signup_read_model to track user creation.
 */
class ProjectPatientUserCreated
{
    public function handle(PatientUserCreated $event): void
    {
        // Check if user already exists with this email
        $existingUser = User::where('email', $event->email)->first();

        if ($existingUser) {
            // User already exists, don't create duplicate
            return;
        }

        // Create new user with patient role
        $user = User::create([
            'id' => $event->userId,
            'name' => $event->email, // Use email as name initially
            'email' => $event->email,
            'password' => Hash::make(\Illuminate\Support\Str::random(16)),
            'status' => 'active',
        ]);

        // Assign patient role using Spatie's HasRoles trait
        $user->assignRole('patient');

        // Update signup_read_model to track user creation
        $this->updateSignupReadModel($event, $user);
    }

    private function updateSignupReadModel(PatientUserCreated $event, User $user): void
    {
        // Get the signup_read_model class dynamically
        $signupReadModelClass = 'App\Models\SignupReadModel';

        if (!class_exists($signupReadModelClass)) {
            return;
        }

        // Find the signup record by signup_uuid
        $signupReadModel = $signupReadModelClass::where('signup_uuid', $event->signupId)->first();

        if ($signupReadModel) {
            $signupReadModel->update([
                'user_id' => $user->id,
                'user_email' => $event->email,
                'user_created_at' => now(),
            ]);
        }
    }
}

