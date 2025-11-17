<?php

namespace App\Application\Patient;

use Illuminate\Contracts\Pagination\Paginator;

interface PatientListFinder
{
    public function paginate(?string $search, int $perPage): Paginator;

    public function count(?string $search): int;
}

