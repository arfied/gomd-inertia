<?php

namespace App\Application\MedicationCatalog;

use App\Domain\MedicationCatalog\Events\FormularyCreated;
use App\Domain\MedicationCatalog\Events\FormularyUpdated;
use App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary;
use App\Domain\MedicationCatalog\Events\MedicationCreated;
use App\Domain\MedicationCatalog\Events\MedicationRemovedFromFormulary;
use App\Domain\MedicationCatalog\Events\MedicationUpdated;
use App\Models\Formulary;
use App\Models\FormularyMedication;
use App\Models\Medication;
use App\Models\MedicationSearchIndex;

/**
 * Eloquent implementation of the medication catalog projector.
 *
 * Projects domain events into read models for medications, formularies,
 * and search indexes.
 */
class EloquentMedicationCatalogProjector implements MedicationCatalogProjector
{
    public function projectMedicationCreated(MedicationCreated $event): void
    {
        $payload = $event->payload;

        Medication::updateOrCreate(
            ['id' => $payload['id'] ?? null],
            $payload
        );

        // Also update the search index
        if (isset($payload['id'])) {
            MedicationSearchIndex::updateOrCreate(
                ['medication_id' => $payload['id']],
                [
                    'name' => $payload['name'] ?? null,
                    'generic_name' => $payload['generic_name'] ?? null,
                    'drug_class' => $payload['drug_class'] ?? null,
                    'description' => $payload['description'] ?? null,
                    'type' => $payload['type'] ?? null,
                    'status' => $payload['status'] ?? 'active',
                    'unit_price' => $payload['unit_price'] ?? null,
                    'requires_prescription' => $payload['requires_prescription'] ?? true,
                    'controlled_substance' => $payload['controlled_substance'] ?? false,
                ]
            );
        }
    }

    public function projectMedicationUpdated(MedicationUpdated $event): void
    {
        $payload = $event->payload;

        if (isset($payload['id'])) {
            Medication::where('id', $payload['id'])->update($payload);

            // Update search index
            MedicationSearchIndex::where('medication_id', $payload['id'])->update([
                'name' => $payload['name'] ?? null,
                'generic_name' => $payload['generic_name'] ?? null,
                'drug_class' => $payload['drug_class'] ?? null,
                'description' => $payload['description'] ?? null,
                'type' => $payload['type'] ?? null,
                'status' => $payload['status'] ?? 'active',
                'unit_price' => $payload['unit_price'] ?? null,
                'requires_prescription' => $payload['requires_prescription'] ?? true,
                'controlled_substance' => $payload['controlled_substance'] ?? false,
            ]);
        }
    }

    public function projectFormularyCreated(FormularyCreated $event): void
    {
        $payload = $event->payload;

        Formulary::updateOrCreate(
            ['uuid' => $event->aggregateUuid],
            [
                'name' => $payload['name'] ?? null,
                'description' => $payload['description'] ?? null,
                'organization_id' => $payload['organization_id'] ?? null,
                'type' => $payload['type'] ?? null,
                'status' => $payload['status'] ?? 'active',
            ]
        );
    }

    public function projectFormularyUpdated(FormularyUpdated $event): void
    {
        $payload = $event->payload;

        Formulary::where('uuid', $event->aggregateUuid)->update([
            'name' => $payload['name'] ?? null,
            'description' => $payload['description'] ?? null,
            'organization_id' => $payload['organization_id'] ?? null,
            'type' => $payload['type'] ?? null,
            'status' => $payload['status'] ?? null,
        ]);
    }

    public function projectMedicationAddedToFormulary(MedicationAddedToFormulary $event): void
    {
        $payload = $event->payload;
        $formulary = Formulary::where('uuid', $event->aggregateUuid)->first();

        if ($formulary && isset($payload['medication_uuid'])) {
            $medication = Medication::where('id', $payload['medication_uuid'])->first();

            if ($medication) {
                FormularyMedication::updateOrCreate(
                    [
                        'formulary_id' => $formulary->id,
                        'medication_id' => $medication->id,
                    ],
                    [
                        'tier' => $payload['tier'] ?? null,
                        'requires_pre_authorization' => $payload['requires_pre_authorization'] ?? false,
                        'notes' => $payload['notes'] ?? null,
                    ]
                );
            }
        }
    }

    public function projectMedicationRemovedFromFormulary(MedicationRemovedFromFormulary $event): void
    {
        $payload = $event->payload;
        $formulary = Formulary::where('uuid', $event->aggregateUuid)->first();

        if ($formulary && isset($payload['medication_uuid'])) {
            FormularyMedication::where('formulary_id', $formulary->id)
                ->where('medication_id', $payload['medication_uuid'])
                ->delete();
        }
    }
}

