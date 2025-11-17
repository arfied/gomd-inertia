<?php

namespace App\Application\Patient;

use App\Models\PatientEnrollment;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        $latestSubscriptions = DB::table('subscriptions')
            ->select('user_id', DB::raw('MAX(id) as latest_subscription_id'))
            ->groupBy('user_id');

        $query = PatientEnrollment::query()
            ->join('users', 'patient_enrollments.user_id', '=', 'users.id')
            ->leftJoinSub($latestSubscriptions, 'latest_subscriptions', function ($join) {
                $join->on('latest_subscriptions.user_id', '=', 'users.id');
            })
            ->leftJoin('subscriptions', 'subscriptions.id', '=', 'latest_subscriptions.latest_subscription_id')
            ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->select([
                'patient_enrollments.patient_uuid',
                'patient_enrollments.enrolled_at',
                'users.id as user_id',
                'users.fname',
                'users.lname',
                'users.email',
                'users.status',
                'subscriptions.status as subscription_status',
                'subscriptions.is_trial as subscription_is_trial',
                'subscription_plans.name as subscription_plan_name',
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

