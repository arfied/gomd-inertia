<?php

namespace App\Application\Patient;

/**
 * Finder for a patient's medical history snapshot backed by the
 * existing legacy medical history tables.
 */
interface PatientMedicalHistoryFinder
{
    /**
     * Build a normalized medical history snapshot for the given user.
     *
     * @return array{
     *   allergies: array<int, array<string, mixed>>,
     *   conditions: array<int, array<string, mixed>>,
     *   medications: array<int, array<string, mixed>>,
     *   surgical_history: array<string, mixed>|null,
     *   family_history: array<string, mixed>|null
     * }
     */
    public function findByUserId(int $userId): array;
}

