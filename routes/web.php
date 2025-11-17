<?php

use App\Http\Controllers\PatientEnrollmentController;
use App\Http\Controllers\PatientActivityController;
use App\Http\Controllers\PatientSubscriptionController;
use App\Http\Controllers\PatientTimelineController;
use App\Http\Controllers\PatientDemographicsController;
use App\Http\Controllers\PatientDocumentsController;
use App\Http\Controllers\PatientListController;
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

    Route::get('patients', [PatientListController::class, 'index'])
        ->name('patients.index');

    Route::get('patients/count', [PatientListController::class, 'count'])
        ->name('patients.count');
});

require __DIR__.'/settings.php';
