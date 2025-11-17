<?php

namespace App\Application\Patient;

use App\Models\PatientEnrollment;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EloquentPatientListFinder implements PatientListFinder
{
    public function paginate(?string $search, int $perPage, array $filters = []): Paginator
    {
        return $this->baseQuery($search, $filters)->simplePaginate($perPage);
    }

    public function count(?string $search, array $filters = []): int
    {
        return $this->baseQuery($search, $filters)->count();
    }

    protected function baseQuery(?string $search, array $filters = []): Builder
    {
        $latestSubscriptions = DB::table('subscriptions')
            ->select('user_id', DB::raw('MAX(id) as latest_subscription_id'))
            ->groupBy('user_id');

        $documentsSummary = DB::table('medical_records')
            ->select('patient_id as user_id', DB::raw('COUNT(*) as documents_count'))
            ->groupBy('patient_id');

        $medicalHistorySource = DB::table('allergies')
            ->select('user_id', DB::raw('1 as weight'))
            ->unionAll(
                DB::table('medical_conditions')->select('patient_id as user_id', DB::raw('1 as weight'))
            )
            ->unionAll(
                DB::table('medication_histories')->select('user_id', DB::raw('1 as weight'))
            )
            ->unionAll(
                DB::table('medical_surgical_histories')->select('patient_id as user_id', DB::raw('1 as weight'))
            )
            ->unionAll(
                DB::table('family_medical_histories')->select('patient_id as user_id', DB::raw('1 as weight'))
            );

        $medicalHistorySummary = DB::query()
            ->fromSub($medicalHistorySource, 'mh')
            ->select('user_id', DB::raw('COUNT(*) as medical_history_count'))
            ->groupBy('user_id');

        $query = PatientEnrollment::query()
            ->join('users', 'patient_enrollments.user_id', '=', 'users.id')
            ->leftJoinSub($latestSubscriptions, 'latest_subscriptions', function ($join) {
                $join->on('latest_subscriptions.user_id', '=', 'users.id');
            })
            ->leftJoin('subscriptions', 'subscriptions.id', '=', 'latest_subscriptions.latest_subscription_id')
            ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->leftJoinSub($documentsSummary, 'documents_summary', function ($join) {
                $join->on('documents_summary.user_id', '=', 'users.id');
            })
            ->leftJoinSub($medicalHistorySummary, 'medical_history_summary', function ($join) {
                $join->on('medical_history_summary.user_id', '=', 'users.id');
            })
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
                DB::raw('COALESCE(documents_summary.documents_count, 0) as documents_count'),
                DB::raw('COALESCE(medical_history_summary.medical_history_count, 0) as medical_history_count'),
            ])
            ->orderByDesc('patient_enrollments.enrolled_at');

        if ($search !== null && $search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('users.fname', 'like', "%{$search}%")
                    ->orWhere('users.lname', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['has_active_subscription'])) {
            $query->where('subscriptions.status', '=', 'active');
        }

        if (! empty($filters['has_documents'])) {
            $query->where('documents_summary.documents_count', '>', 0);
        }

        if (! empty($filters['has_medical_history'])) {
            $query->where('medical_history_summary.medical_history_count', '>', 0);
        }

        return $query;
    }
}

