<?php

namespace App\Application\MedicationCatalog;

use App\Domain\MedicationCatalog\Events\FormularyCreated;
use App\Domain\MedicationCatalog\Events\FormularyUpdated;
use App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary;
use App\Domain\MedicationCatalog\Events\MedicationCreated;
use App\Domain\MedicationCatalog\Events\MedicationRemovedFromFormulary;
use App\Domain\MedicationCatalog\Events\MedicationUpdated;

/**
 * Interface for medication catalog projectors.
 *
 * Projectors handle the projection of domain events into read models
 * for the medication catalog bounded context.
 */
interface MedicationCatalogProjector
{
    /**
     * Project a MedicationCreated event.
     */
    public function projectMedicationCreated(MedicationCreated $event): void;

    /**
     * Project a MedicationUpdated event.
     */
    public function projectMedicationUpdated(MedicationUpdated $event): void;

    /**
     * Project a FormularyCreated event.
     */
    public function projectFormularyCreated(FormularyCreated $event): void;

    /**
     * Project a FormularyUpdated event.
     */
    public function projectFormularyUpdated(FormularyUpdated $event): void;

    /**
     * Project a MedicationAddedToFormulary event.
     */
    public function projectMedicationAddedToFormulary(MedicationAddedToFormulary $event): void;

    /**
     * Project a MedicationRemovedFromFormulary event.
     */
    public function projectMedicationRemovedFromFormulary(MedicationRemovedFromFormulary $event): void;
}

