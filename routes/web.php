<?php

use App\Http\Controllers\PatientEnrollmentController;
use App\Http\Controllers\PatientActivityController;
use App\Http\Controllers\PatientSubscriptionController;
use App\Http\Controllers\PatientTimelineController;
use App\Http\Controllers\PatientDemographicsController;
use App\Http\Controllers\PatientDocumentsController;
use App\Http\Controllers\PatientListController;
use App\Http\Controllers\PatientMedicalHistoryController;
use App\Http\Controllers\PatientSelfMedicalHistoryController;
use App\Http\Controllers\StaffPatientDocumentsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

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

    Route::get('patient/subscription', [PatientSubscriptionController::class, 'show'])
        ->name('patient.subscription.show');

    Route::get('patient/activity/recent', [PatientActivityController::class, 'index'])
        ->name('patient.activity.recent');

    Route::get('patient/events/timeline', [PatientTimelineController::class, 'index'])
        ->name('patient.events.timeline');

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

    Route::post('patients/{patientUuid}/documents', [StaffPatientDocumentsController::class, 'store'])
        ->name('patients.documents.store');

    Route::post('patients/{patientUuid}/medical-history/allergies', [PatientMedicalHistoryController::class, 'storeAllergy'])
        ->name('patients.medical-history.allergies.store');

    Route::post('patients/{patientUuid}/medical-history/conditions', [PatientMedicalHistoryController::class, 'storeCondition'])
        ->name('patients.medical-history.conditions.store');

    Route::post('patients/{patientUuid}/medical-history/medications', [PatientMedicalHistoryController::class, 'storeMedication'])
        ->name('patients.medical-history.medications.store');

    Route::post('patients/{patientUuid}/medical-history/visit-summary', [PatientMedicalHistoryController::class, 'storeVisitSummary'])
        ->name('patients.medical-history.visit-summary.store');
});

require __DIR__.'/settings.php';
