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
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\Admin\SubscriptionConfigurationController;
use App\Http\Controllers\Admin\FailedRenewalsController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\ConditionController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\QuestionnaireController;
use App\Http\Controllers\Api\QuestionnaireSubmissionController;
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

// Signup routes - PUBLIC (no auth required)
Route::get('signup', function () {
    return Inertia::render('Signup');
})->name('signup.index');

Route::prefix('signup')->name('signup.')->group(function () {
    Route::post('start', [App\Http\Controllers\Signup\SignupController::class, 'start'])->name('start');
    Route::post('select-medication', [App\Http\Controllers\Signup\SignupController::class, 'selectMedication'])->name('select-medication');
    Route::post('select-condition', [App\Http\Controllers\Signup\SignupController::class, 'selectCondition'])->name('select-condition');
    Route::post('select-plan', [App\Http\Controllers\Signup\SignupController::class, 'selectPlan'])->name('select-plan');
    Route::post('complete-questionnaire', [App\Http\Controllers\Signup\SignupController::class, 'completeQuestionnaire'])->name('complete-questionnaire');
    Route::post('process-payment', [App\Http\Controllers\Signup\SignupController::class, 'processPayment'])->name('process-payment');
    Route::post('create-subscription', [App\Http\Controllers\Signup\SignupController::class, 'createSubscription'])->name('create-subscription');
    Route::post('fail', [App\Http\Controllers\Signup\SignupController::class, 'fail'])->name('fail');
    Route::get('{signupId}/status', [App\Http\Controllers\Signup\SignupController::class, 'status'])->name('status');
});

// Public API routes for signup flow
Route::prefix('api')->group(function () {
    Route::get('medications', [MedicationController::class, 'index'])->name('api.medications.index');
    Route::get('medications/{medication}', [MedicationController::class, 'show'])->name('api.medications.show');
    Route::get('conditions', [ConditionController::class, 'index'])->name('api.conditions.index');
    Route::get('conditions/{condition}', [ConditionController::class, 'show'])->name('api.conditions.show');
    Route::get('plans', [SubscriptionPlanController::class, 'index'])->name('api.plans.index');
    Route::get('plans/{plan}', [SubscriptionPlanController::class, 'show'])->name('api.plans.show');
    Route::get('questionnaires', [QuestionnaireController::class, 'index'])->name('api.questionnaires.index');
    Route::post('questionnaires/submit', [QuestionnaireSubmissionController::class, 'submit'])->name('api.questionnaires.submit');
});

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

    // Billing Page
    Route::get('billing', function () {
        return Inertia::render('billing/BillingPage');
    })->name('billing.index');

    // Payment Methods API Routes
    Route::prefix('api/patient/payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])
            ->name('api.payment-methods.index');

        Route::post('/', [PaymentMethodController::class, 'store'])
            ->name('api.payment-methods.store');

        Route::get('{paymentMethod}', [PaymentMethodController::class, 'show'])
            ->name('api.payment-methods.show');

        Route::patch('{paymentMethod}', [PaymentMethodController::class, 'update'])
            ->name('api.payment-methods.update');

        Route::delete('{paymentMethod}', [PaymentMethodController::class, 'destroy'])
            ->name('api.payment-methods.destroy');

        Route::post('{paymentMethod}/set-default', [PaymentMethodController::class, 'setDefault'])
            ->name('api.payment-methods.set-default');
    });


    // Clinical routes
    Route::prefix('clinical')->name('clinical.')->group(function () {
        Route::get('questionnaires', [App\Http\Controllers\Clinical\QuestionnaireController::class, 'index'])->name('questionnaires.index');
        Route::post('questionnaires', [App\Http\Controllers\Clinical\QuestionnaireController::class, 'store'])->name('questionnaires.store');
        Route::get('questionnaires/{uuid}', [App\Http\Controllers\Clinical\QuestionnaireController::class, 'show'])->name('questionnaires.show');
        Route::post('questionnaires/{uuid}/responses', [App\Http\Controllers\Clinical\QuestionnaireController::class, 'submitResponse'])->name('questionnaires.submit-response');

        Route::get('notes', [App\Http\Controllers\Clinical\ClinicalNoteController::class, 'index'])->name('notes.index');
        Route::post('notes', [App\Http\Controllers\Clinical\ClinicalNoteController::class, 'store'])->name('notes.store');
        Route::get('notes/{uuid}', [App\Http\Controllers\Clinical\ClinicalNoteController::class, 'show'])->name('notes.show');

        Route::get('consultations', [App\Http\Controllers\Clinical\ConsultationController::class, 'index'])->name('consultations.index');
        Route::post('consultations', [App\Http\Controllers\Clinical\ConsultationController::class, 'store'])->name('consultations.store');
        Route::get('consultations/{uuid}', [App\Http\Controllers\Clinical\ConsultationController::class, 'show'])->name('consultations.show');
    });

    // Compliance routes
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('audit-trail', [App\Http\Controllers\Compliance\AuditTrailController::class, 'index'])->name('audit-trail.index');
        Route::get('audit-trail/export', [App\Http\Controllers\Compliance\AuditTrailController::class, 'export'])->name('audit-trail.export');
        Route::get('audit-trail/{uuid}', [App\Http\Controllers\Compliance\AuditTrailController::class, 'show'])->name('audit-trail.show');

        Route::get('dashboard', [App\Http\Controllers\Compliance\DashboardController::class, 'index'])->name('dashboard');

        Route::get('consents', [App\Http\Controllers\Compliance\ConsentController::class, 'index'])->name('consents.index');
        Route::post('consents', [App\Http\Controllers\Compliance\ConsentController::class, 'store'])->name('consents.store');
        Route::get('consents/{uuid}', [App\Http\Controllers\Compliance\ConsentController::class, 'show'])->name('consents.show');

        Route::get('licenses', [App\Http\Controllers\Compliance\LicenseController::class, 'index'])->name('licenses.index');
        Route::post('licenses', [App\Http\Controllers\Compliance\LicenseController::class, 'store'])->name('licenses.store');
        Route::get('licenses/{uuid}', [App\Http\Controllers\Compliance\LicenseController::class, 'show'])->name('licenses.show');
    });



    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('subscription-configuration', [SubscriptionConfigurationController::class, 'show'])
            ->name('subscription-configuration.show');

        Route::get('subscription-configuration/config', [SubscriptionConfigurationController::class, 'getConfiguration'])
            ->name('subscription-configuration.get');

        Route::post('subscription-configuration/retry', [SubscriptionConfigurationController::class, 'updateRetryConfiguration'])
            ->name('subscription-configuration.update-retry');

        Route::post('subscription-configuration/rate-limits', [SubscriptionConfigurationController::class, 'updateRateLimitConfiguration'])
            ->name('subscription-configuration.update-rate-limits');

        // Failed renewals routes
        Route::get('failed-renewals', [FailedRenewalsController::class, 'index'])
            ->name('failed-renewals.index');

        Route::get('failed-renewals/{sagaUuid}', [FailedRenewalsController::class, 'show'])
            ->name('failed-renewals.show');
    });
});

require __DIR__.'/settings.php';
