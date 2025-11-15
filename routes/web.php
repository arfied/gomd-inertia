<?php

use App\Http\Controllers\PatientEnrollmentController;
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
});

require __DIR__.'/settings.php';
