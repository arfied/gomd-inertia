<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientDemographicsUpdated;
use App\Models\User;

class EloquentPatientDemographicsProjector implements PatientDemographicsProjector
{
    public function project(PatientDemographicsUpdated $event): void
    {
        $userId = $event->payload['user_id'] ?? null;

        if ($userId === null) {
            return;
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if ($user === null) {
            return;
        }

        $fields = [
            'fname',
            'lname',
            'gender',
            'dob',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'phone',
            'mobile_phone',
        ];

        $dirty = false;

        foreach ($fields as $field) {
            if (array_key_exists($field, $event->payload)) {
                $user->{$field} = $event->payload[$field];
                $dirty = true;
            }
        }

        if ($dirty) {
            $user->save();
        }
    }
}

