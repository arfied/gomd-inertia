<?php

namespace App\Listeners;

use App\Application\MedicationCatalog\MedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\FormularyUpdated;

class ProjectFormularyUpdated
{
    public function __construct(
        private MedicationCatalogProjector $projector,
    ) {
    }

    public function handle(FormularyUpdated $event): void
    {
        $this->projector->projectFormularyUpdated($event);
    }
}

