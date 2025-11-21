<?php

use App\Http\Controllers\PatientEnrollmentController;
use App\Http\Controllers\PatientActivityController;
use App\Http\Controllers\PatientSubscriptionController;
use App\Http\Controllers\PatientTimelineController;
use App\Http\Controllers\PatientOrderTimelineController;
use App\Http\Controllers\PatientDemographicsController;
use App\Http\Controllers\PatientDocumentsController;
use App\Http\Controllers\PatientListController;
use App\Http\Controllers\PatientMedicalHistoryController;
use App\Http\Controllers\PatientOrdersController;
use App\Http\Controllers\PatientSelfMedicalHistoryController;
use App\Http\Controllers\StaffPatientDocumentsController;
use App\Http\Controllers\StaffPatientOrdersController;
use App\Http\Controllers\StaffPatientOrderTimelineController;
use App\Http\Controllers\StaffPatientSubscriptionController;
use App\Http\Controllers\DoctorPatientPrescriptionsController;
use App\Http\Controllers\AgentCommissionDashboardController;
use App\Http\Controllers\AgentReferralLinksController;
use App\Http\Controllers\ReferralTrackingController;
use App\Http\Controllers\ReferralNetworkController;
use App\Http\Controllers\SubscriptionAnalyticsDashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Public referral landing page - tracks clicks and redirects
Route::get('/ref/{referralCode}', [ReferralTrackingController::class, 'landingPage'])
    ->name('referral.landing');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('dashboard/patients', function (\Illuminate\Http\Request $request) {
        $user = $request->user();

        $isStaffOrAdmin = $user
            && ($user->hasAnyRole(['admin', 'staff']) || in_array($user->role, ['admin', 'staff'], true));

        abort_unless($isStaffOrAdmin, 403);

        return Inertia::render('staff/Patients/Index');
    })->name('dashboard.patients');
    Route::get('patient/enrollment', [PatientEnrollmentController::class, 'show'])
        ->name('patient.enrollment.show');

    Route::post('patient/enrollment', [PatientEnrollmentController::class, 'store'])
        ->name('patient.enrollment.store');

    Route::get('patient/demographics', [PatientDemographicsController::class, 'show'])
        ->name('patient.demographics.show');

    Route::put('patient/demographics', [PatientDemographicsController::class, 'update'])
        ->name('patient.demographics.update');

    Route::get('patient/documents', [PatientDocumentsController::class, 'index'])
        ->name('patient.documents.index');

    Route::post('patient/documents', [PatientDocumentsController::class, 'store'])
        ->name('patient.documents.store');

    Route::get('patient/orders', [PatientOrdersController::class, 'index'])
        ->name('patient.orders.index');


    Route::get('patient/subscription', [PatientSubscriptionController::class, 'show'])
        ->name('patient.subscription.show');

    Route::post('patient/subscription/cancel', [PatientSubscriptionController::class, 'cancel'])
        ->name('patient.subscription.cancel');

    Route::post('patient/subscription/renew', [PatientSubscriptionController::class, 'renew'])
        ->middleware('rate.limit.subscription.renewal')
        ->name('patient.subscription.renew');

    Route::get('patient/activity/recent', [PatientActivityController::class, 'index'])
        ->name('patient.activity.recent');

    Route::get('patient/events/timeline', [PatientTimelineController::class, 'index'])
        ->name('patient.events.timeline');

    Route::get('patient/orders/timeline', [PatientOrderTimelineController::class, 'index'])
        ->name('patient.orders.timeline');

    Route::get('patient/medical-history', [PatientSelfMedicalHistoryController::class, 'show'])
        ->name('patient.medical-history.show');

    Route::post('patient/medical-history/allergies', [PatientSelfMedicalHistoryController::class, 'storeAllergy'])
        ->name('patient.medical-history.allergies.store');

    Route::post('patient/medical-history/conditions', [PatientSelfMedicalHistoryController::class, 'storeCondition'])
        ->name('patient.medical-history.conditions.store');

    Route::post('patient/medical-history/medications', [PatientSelfMedicalHistoryController::class, 'storeMedication'])
        ->name('patient.medical-history.medications.store');

    Route::post('patient/medical-history/visit-summary', [PatientSelfMedicalHistoryController::class, 'storeVisitSummary'])
        ->name('patient.medical-history.visit-summary.store');

    Route::get('patients', [PatientListController::class, 'index'])
        ->name('patients.index');

    Route::get('patients/count', [PatientListController::class, 'count'])
        ->name('patients.count');

    Route::get('patients/{patientUuid}', [PatientListController::class, 'show'])
        ->name('patients.show');

    Route::get('patients/{patientUuid}/documents', [StaffPatientDocumentsController::class, 'index'])
        ->name('patients.documents.index');

    Route::post('patients/{patientUuid}/orders/{order}/prescriptions', [DoctorPatientPrescriptionsController::class, 'store'])
        ->name('patients.orders.prescriptions.store');

    Route::get('patients/{patientUuid}/orders', [StaffPatientOrdersController::class, 'index'])
        ->name('patients.orders.index');

    Route::get('patients/{patientUuid}/orders/timeline', [StaffPatientOrderTimelineController::class, 'index'])
        ->name('patients.orders.timeline');

    Route::post('patients/{patientUuid}/documents', [StaffPatientDocumentsController::class, 'store'])
        ->name('patients.documents.store');

    Route::post('patients/{patientUuid}/subscription/renew', [StaffPatientSubscriptionController::class, 'renew'])
        ->middleware('rate.limit.subscription.renewal')
        ->name('patients.subscription.renew');

    Route::post('patients/{patientUuid}/medical-history/allergies', [PatientMedicalHistoryController::class, 'storeAllergy'])
        ->name('patients.medical-history.allergies.store');

    Route::post('patients/{patientUuid}/medical-history/conditions', [PatientMedicalHistoryController::class, 'storeCondition'])
        ->name('patients.medical-history.conditions.store');

    Route::post('patients/{patientUuid}/medical-history/medications', [PatientMedicalHistoryController::class, 'storeMedication'])
        ->name('patients.medical-history.medications.store');

    Route::post('patients/{patientUuid}/medical-history/visit-summary', [PatientMedicalHistoryController::class, 'storeVisitSummary'])
        ->name('patients.medical-history.visit-summary.store');

    Route::get('agent/commission/dashboard', [AgentCommissionDashboardController::class, 'show'])
        ->name('agent.commission.dashboard');

    Route::get('agent/referral-links', [AgentReferralLinksController::class, 'index'])
        ->name('agent.referral-links.index');

    // Referral tracking routes
    Route::post('referral/track', [ReferralTrackingController::class, 'trackClick'])
        ->name('referral.track');

    Route::post('referral/convert', [ReferralTrackingController::class, 'recordConversion'])
        ->name('referral.convert');

    Route::get('referral/{referralCode}', [ReferralTrackingController::class, 'show'])
        ->name('referral.show');

    // Referral network visualization routes
    Route::get('agent/{agentId}/referral-network/hierarchy', [ReferralNetworkController::class, 'hierarchy'])
        ->name('agent.referral-network.hierarchy');

    Route::get('agent/{agentId}/referral-network/performance', [ReferralNetworkController::class, 'performance'])
        ->name('agent.referral-network.performance');

    // Agent Analytics Dashboard Page
    Route::get('agent/analytics', function () {
        return Inertia::render('agent/AnalyticsDashboard');
    })->name('agent.analytics');

    // Subscription Analytics API Routes
    Route::prefix('analytics/subscription')->group(function () {
        Route::get('mrr', [SubscriptionAnalyticsDashboardController::class, 'mrr'])
            ->name('analytics.subscription.mrr');

        Route::get('churn', [SubscriptionAnalyticsDashboardController::class, 'churn'])
            ->name('analytics.subscription.churn');

        Route::get('ltv', [SubscriptionAnalyticsDashboardController::class, 'ltv'])
            ->name('analytics.subscription.ltv');

        Route::get('dashboard', [SubscriptionAnalyticsDashboardController::class, 'dashboard'])
            ->name('analytics.subscription.dashboard');
    });
});

require __DIR__.'/settings.php';
