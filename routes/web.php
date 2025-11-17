<?php

use App\Http\Controllers\PatientEnrollmentController;
use App\Http\Controllers\PatientActivityController;
use App\Http\Controllers\PatientSubscriptionController;
use App\Http\Controllers\PatientTimelineController;
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

    Route::get('patient/subscription', [PatientSubscriptionController::class, 'show'])
        ->name('patient.subscription.show');

    Route::get('patient/activity/recent', [PatientActivityController::class, 'index'])
        ->name('patient.activity.recent');

    Route::get('patient/events/timeline', [PatientTimelineController::class, 'index'])
        ->name('patient.events.timeline');
});

require __DIR__.'/settings.php';
