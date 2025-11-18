<?php

namespace App\Listeners;

use App\Application\MedicationCatalog\MedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\MedicationCreated;

class ProjectMedicationCreated
{
    public function __construct(
        private MedicationCatalogProjector $projector,
    ) {
    }

    public function handle(MedicationCreated $event): void
    {
        $this->projector->projectMedicationCreated($event);
    }
}

