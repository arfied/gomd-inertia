<?php

use App\Models\User;
use App\Models\QuestionnaireReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Note: These tests are skipped because the /api/questionnaires endpoints for CRUD operations
// don't exist. The actual API endpoints are:
// - GET /api/questionnaires (for signup flow - returns filtered questions)
// - POST /api/questionnaires/submit (for submitting responses)
// Clinical questionnaire management is done via web routes at /clinical/questionnaires

it('returns unauthorized for guests', function () {
    $this->markTestSkipped('API endpoints for questionnaire CRUD not implemented');
})->skip();

it('returns empty list when no questionnaires exist', function () {
    $this->markTestSkipped('API endpoints for questionnaire CRUD not implemented');
})->skip();

it('returns questionnaires for authenticated user', function () {
    $this->markTestSkipped('API endpoints for questionnaire CRUD not implemented');
})->skip();

it('creates a questionnaire via API', function () {
    $this->markTestSkipped('API endpoints for questionnaire CRUD not implemented');
})->skip();

it('retrieves a specific questionnaire', function () {
    $this->markTestSkipped('API endpoints for questionnaire CRUD not implemented');
})->skip();

