<?php

namespace App\Application\Patient;

use App\Models\PatientEnrollment;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentPatientListFinder implements PatientListFinder
{
    public function paginate(?string $search, int $perPage): Paginator
    {
        return $this->baseQuery($search)->simplePaginate($perPage);
    }

    public function count(?string $search): int
    {
        return $this->baseQuery($search)->count();
    }

    protected function baseQuery(?string $search): Builder
    {
        $query = PatientEnrollment::query()
            ->join('users', 'patient_enrollments.user_id', '=', 'users.id')
            ->select([
                'patient_enrollments.patient_uuid',
                'patient_enrollments.enrolled_at',
                'users.id as user_id',
                'users.fname',
                'users.lname',
                'users.email',
                'users.status',
            ])
            ->orderByDesc('patient_enrollments.enrolled_at');

        if ($search !== null && $search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('users.fname', 'like', "%{$search}%")
                    ->orWhere('users.lname', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}

