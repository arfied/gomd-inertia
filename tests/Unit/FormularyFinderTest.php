<?php

namespace Tests\Unit;

use App\Application\MedicationCatalog\FormularyFinder;
use App\Models\Formulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormularyFinderTest extends TestCase
{
    use RefreshDatabase;
    private FormularyFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->finder = app(FormularyFinder::class);
    }

    public function test_search_returns_paginated_results(): void
    {
        Formulary::factory()->count(3)->create(['status' => 'active']);

        $results = $this->finder->search(status: 'active', perPage: 15);

        $this->assertCount(3, $results->items());
    }

    public function test_search_filters_by_organization(): void
    {
        Formulary::factory()->create([
            'organization_id' => 'org-1',
            'status' => 'active',
        ]);

        Formulary::factory()->create([
            'organization_id' => 'org-2',
            'status' => 'active',
        ]);

        $results = $this->finder->search(organizationId: 'org-1', status: 'active');

        $this->assertCount(1, $results->items());
        $this->assertEquals('org-1', $results->items()[0]->organization_id);
    }

    public function test_search_filters_by_type(): void
    {
        Formulary::factory()->create([
            'type' => 'insurance',
            'status' => 'active',
        ]);

        Formulary::factory()->create([
            'type' => 'hospital',
            'status' => 'active',
        ]);

        $results = $this->finder->search(type: 'insurance', status: 'active');

        $this->assertCount(1, $results->items());
        $this->assertEquals('insurance', $results->items()[0]->type);
    }

    public function test_search_filters_by_query_name(): void
    {
        Formulary::factory()->create([
            'name' => 'Blue Cross Formulary',
            'status' => 'active',
        ]);

        Formulary::factory()->create([
            'name' => 'Aetna Formulary',
            'status' => 'active',
        ]);

        $results = $this->finder->search(query: 'Blue', status: 'active');

        $this->assertCount(1, $results->items());
        $this->assertStringContainsString('Blue', $results->items()[0]->name);
    }

    public function test_search_filters_by_query_description(): void
    {
        Formulary::factory()->create([
            'name' => 'Formulary A',
            'description' => 'Covers cardiac medications',
            'status' => 'active',
        ]);

        Formulary::factory()->create([
            'name' => 'Formulary B',
            'description' => 'Covers antibiotics',
            'status' => 'active',
        ]);

        $results = $this->finder->search(query: 'cardiac', status: 'active');

        $this->assertCount(1, $results->items());
    }

    public function test_count_returns_correct_total(): void
    {
        Formulary::factory()->count(5)->create(['status' => 'active']);

        $count = $this->finder->count(status: 'active');

        $this->assertEquals(5, $count);
    }

    public function test_search_respects_status_filter(): void
    {
        Formulary::factory()->create(['status' => 'active']);
        Formulary::factory()->create(['status' => 'inactive']);

        $results = $this->finder->search(status: 'active');

        $this->assertCount(1, $results->items());
    }
}

