<?php

namespace App\Concerns;

trait HandlesCommissionTiers
{
    /**
     * Check if the user is an LOA (Licensed Only Agent / referrer/encoder).
     *
     * @return bool
     */
    public function isLOA(): bool
    {
        return $this->hasRole('loa');
    }
}
