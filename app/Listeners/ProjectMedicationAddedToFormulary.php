<?php

namespace App\Listeners;

use App\Application\MedicationCatalog\MedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary;

class ProjectMedicationAddedToFormulary
{
    public function __construct(
        private MedicationCatalogProjector $projector,
    ) {
    }

    public function handle(MedicationAddedToFormulary $event): void
    {
        $this->projector->projectMedicationAddedToFormulary($event);
    }
}

