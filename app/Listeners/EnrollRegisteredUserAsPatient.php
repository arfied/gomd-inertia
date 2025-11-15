<?php

namespace App\Listeners;

use App\Application\Patient\PatientEnrollmentService;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

/**
 * Listener that automatically enrolls newly registered users as patients.
 */
class EnrollRegisteredUserAsPatient
{
    public function __construct(
        private PatientEnrollmentService $patientEnrollmentService,
    ) {
    }

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if ($user instanceof User) {
            $this->patientEnrollmentService->enroll($user, ['source' => 'registration']);
        }
    }
}

