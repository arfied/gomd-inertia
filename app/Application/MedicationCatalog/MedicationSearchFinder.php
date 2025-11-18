<?php

namespace App\Application\MedicationCatalog;

use App\Models\MedicationSearchIndex;
use Illuminate\Pagination\Paginator;

/**
 * Service for searching medications.
 *
 * Provides search functionality for medications with filtering and pagination.
 */
class MedicationSearchFinder
{
    /**
     * Search for medications with optional filters.
     */
    public function search(
        ?string $query = null,
        ?string $drugClass = null,
        ?bool $requiresPrescription = null,
        ?bool $controlledSubstance = null,
        string $status = 'active',
        int $page = 1,
        int $perPage = 15,
        string $sortBy = 'name',
        string $sortOrder = 'asc',
    ): Paginator {
        $queryBuilder = MedicationSearchIndex::query();

        // Filter by status
        $queryBuilder->where('status', $status);

        // Full-text search if query provided
        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('generic_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Filter by drug class
        if ($drugClass !== null) {
            $queryBuilder->where('drug_class', $drugClass);
        }

        // Filter by prescription requirement
        if ($requiresPrescription !== null) {
            $queryBuilder->where('requires_prescription', $requiresPrescription);
        }

        // Filter by controlled substance
        if ($controlledSubstance !== null) {
            $queryBuilder->where('controlled_substance', $controlledSubstance);
        }

        // Sort
        $queryBuilder->orderBy($sortBy, $sortOrder);

        // Paginate using simplePaginate
        return $queryBuilder->simplePaginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get count of medications matching criteria.
     */
    public function count(
        ?string $query = null,
        ?string $drugClass = null,
        ?bool $requiresPrescription = null,
        ?bool $controlledSubstance = null,
        string $status = 'active',
    ): int {
        $queryBuilder = MedicationSearchIndex::query();

        $queryBuilder->where('status', $status);

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('generic_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if ($drugClass !== null) {
            $queryBuilder->where('drug_class', $drugClass);
        }

        if ($requiresPrescription !== null) {
            $queryBuilder->where('requires_prescription', $requiresPrescription);
        }

        if ($controlledSubstance !== null) {
            $queryBuilder->where('controlled_substance', $controlledSubstance);
        }

        return $queryBuilder->count();
    }
}

