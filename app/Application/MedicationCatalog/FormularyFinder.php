<?php

namespace App\Application\MedicationCatalog;

use App\Models\Formulary;
use Illuminate\Pagination\Paginator;

/**
 * Service for searching formularies.
 *
 * Provides search functionality for formularies with filtering and pagination.
 */
class FormularyFinder
{
    /**
     * Search for formularies with optional filters.
     */
    public function search(
        ?string $query = null,
        ?string $organizationId = null,
        ?string $type = null,
        string $status = 'active',
        int $page = 1,
        int $perPage = 15,
        string $sortBy = 'name',
        string $sortOrder = 'asc',
    ): Paginator {
        $queryBuilder = Formulary::query();

        // Filter by status
        $queryBuilder->where('status', $status);

        // Search by name or description
        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Filter by organization
        if ($organizationId !== null) {
            $queryBuilder->where('organization_id', $organizationId);
        }

        // Filter by type
        if ($type !== null) {
            $queryBuilder->where('type', $type);
        }

        // Sort
        $queryBuilder->orderBy($sortBy, $sortOrder);

        // Paginate using simplePaginate
        return $queryBuilder->simplePaginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get count of formularies matching criteria.
     */
    public function count(
        ?string $query = null,
        ?string $organizationId = null,
        ?string $type = null,
        string $status = 'active',
    ): int {
        $queryBuilder = Formulary::query();

        $queryBuilder->where('status', $status);

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if ($organizationId !== null) {
            $queryBuilder->where('organization_id', $organizationId);
        }

        if ($type !== null) {
            $queryBuilder->where('type', $type);
        }

        return $queryBuilder->count();
    }
}

