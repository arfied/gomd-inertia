<?php

namespace App\Application\Patient;

use Illuminate\Contracts\Pagination\Paginator;

interface PatientListFinder
{
    public function paginate(?string $search, int $perPage, array $filters = []): Paginator;

    public function count(?string $search, array $filters = []): int;
}

