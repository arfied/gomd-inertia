<?php

namespace App\Listeners;

use App\Application\MedicationCatalog\MedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\FormularyCreated;

class ProjectFormularyCreated
{
    public function __construct(
        private MedicationCatalogProjector $projector,
    ) {
    }

    public function handle(FormularyCreated $event): void
    {
        $this->projector->projectFormularyCreated($event);
    }
}

