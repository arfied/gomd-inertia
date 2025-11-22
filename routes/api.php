<?php

use App\Http\Controllers\Clinical\QuestionnaireController;
use App\Http\Controllers\Clinical\ClinicalNoteController;
use App\Http\Controllers\Clinical\ConsultationController;
use App\Http\Controllers\Compliance\AuditTrailController;
use App\Http\Controllers\Compliance\ConsentController;
use App\Http\Controllers\Compliance\LicenseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Clinical Routes
    Route::prefix('questionnaires')->group(function () {
        Route::get('/', [QuestionnaireController::class, 'index'])->name('api.questionnaires.index');
        Route::post('/', [QuestionnaireController::class, 'store'])->name('api.questionnaires.store');
        Route::get('{uuid}', [QuestionnaireController::class, 'show'])->name('api.questionnaires.show');
        Route::post('{uuid}/responses', [QuestionnaireController::class, 'submitResponse'])->name('api.questionnaires.submit-response');
    });

    Route::prefix('clinical-notes')->group(function () {
        Route::get('/', [ClinicalNoteController::class, 'index'])->name('api.clinical-notes.index');
        Route::post('/', [ClinicalNoteController::class, 'store'])->name('api.clinical-notes.store');
        Route::get('{uuid}', [ClinicalNoteController::class, 'show'])->name('api.clinical-notes.show');
    });

    Route::prefix('consultations')->group(function () {
        Route::get('/', [ConsultationController::class, 'index'])->name('api.consultations.index');
        Route::post('/', [ConsultationController::class, 'store'])->name('api.consultations.store');
        Route::get('{uuid}', [ConsultationController::class, 'show'])->name('api.consultations.show');
    });

    // Compliance Routes
    Route::prefix('audit-trail')->group(function () {
        Route::get('/', [AuditTrailController::class, 'index'])->name('api.audit-trail.index');
        Route::get('export', [AuditTrailController::class, 'export'])->name('api.audit-trail.export');
        Route::get('{uuid}', [AuditTrailController::class, 'show'])->name('api.audit-trail.show');
    });

    Route::prefix('consents')->group(function () {
        Route::get('/', [ConsentController::class, 'index'])->name('api.consents.index');
        Route::post('/', [ConsentController::class, 'store'])->name('api.consents.store');
        Route::get('{uuid}', [ConsentController::class, 'show'])->name('api.consents.show');
    });

    Route::prefix('licenses')->group(function () {
        Route::get('/', [LicenseController::class, 'index'])->name('api.licenses.index');
        Route::post('/', [LicenseController::class, 'store'])->name('api.licenses.store');
        Route::get('{uuid}', [LicenseController::class, 'show'])->name('api.licenses.show');
    });
});

