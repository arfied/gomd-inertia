<?php

namespace App\Listeners;

use App\Application\MedicationCatalog\MedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\MedicationUpdated;

class ProjectMedicationUpdated
{
    public function __construct(
        private MedicationCatalogProjector $projector,
    ) {
    }

    public function handle(MedicationUpdated $event): void
    {
        $this->projector->projectMedicationUpdated($event);
    }
}

