<?php

namespace App\Listeners;

use App\Application\MedicationCatalog\MedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\MedicationRemovedFromFormulary;

class ProjectMedicationRemovedFromFormulary
{
    public function __construct(
        private MedicationCatalogProjector $projector,
    ) {
    }

    public function handle(MedicationRemovedFromFormulary $event): void
    {
        $this->projector->projectMedicationRemovedFromFormulary($event);
    }
}

