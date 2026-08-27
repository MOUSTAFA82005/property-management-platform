<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * The catalog is the one part of the API a visitor with no account may read.
 * It must work without a token, and it must never show unpublished stock.
 */
class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    public function test_a_visitor_can_browse_published_properties_without_authenticating(): void
    {
        $owner = $this->makeOwner();
        [$published] = $this->makePortfolio($owner, ['name' => 'Public Tower', 'is_published' => true]);

        $response = $this->getJson('/api/properties')->assertOk();

        $this->assertContains($published->id, collect($response->json('data'))->pluck('id')->all());
        $response->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_unpublished_properties_never_appear_in_the_catalog(): void
    {
        $owner = $this->makeOwner();
        [$hidden] = $this->makePortfolio($owner, ['name' => 'Private Tower', 'is_published' => false]);
        [$visible] = $this->makePortfolio($owner, ['name' => 'Public Tower', 'is_published' => true]);

        $ids = collect($this->getJson('/api/properties')->assertOk()->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($visible->id));
        $this->assertFalse($ids->contains($hidden->id));
    }

    public function test_inactive_properties_never_appear_in_the_catalog(): void
    {
        $owner = $this->makeOwner();
        [$inactive] = $this->makePortfolio($owner, ['is_published' => true, 'status' => 'inactive']);

        $ids = collect($this->getJson('/api/properties')->assertOk()->json('data'))->pluck('id');

        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_an_unpublished_property_is_indistinguishable_from_one_that_does_not_exist(): void
    {
        $owner = $this->makeOwner();
        [$hidden] = $this->makePortfolio($owner, ['is_published' => false]);

        $this->getJson("/api/properties/{$hidden->id}")->assertNotFound();
        $this->getJson('/api/properties/999999')->assertNotFound();
    }

    public function test_a_visitor_can_read_a_published_property_and_its_units(): void
    {
        $owner = $this->makeOwner();
        [$property, $building] = $this->makePortfolio($owner, ['is_published' => true]);
        $available = $this->makeUnit($building, ['status' => 'available']);
        $occupied = $this->makeUnit($building, ['status' => 'occupied']);

        $response = $this->getJson("/api/properties/{$property->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $property->id);

        // The detail payload must actually carry the units, not just a count.
        $unitIds = collect($response->json('data.units'))->pluck('id');
        $this->assertTrue($unitIds->contains($available->id));
        $this->assertTrue($unitIds->contains($occupied->id));

        // The default listing is what a visitor can act on.
        $ids = collect($this->getJson("/api/properties/{$property->id}/units")->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($available->id));
        $this->assertFalse($ids->contains($occupied->id));

        // ...but the full inventory is available on request.
        $all = collect($this->getJson("/api/properties/{$property->id}/units?status=occupied")->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($all->contains($occupied->id));
    }

    public function test_units_inside_an_unpublished_property_are_not_public(): void
    {
        $owner = $this->makeOwner();
        [$property, $building] = $this->makePortfolio($owner, ['is_published' => false]);
        $unit = $this->makeUnit($building);

        $this->getJson("/api/units/{$unit->id}")->assertNotFound();
        $this->getJson("/api/properties/{$property->id}/units")->assertNotFound();
    }

    public function test_a_public_unit_can_be_read_without_a_token(): void
    {
        $owner = $this->makeOwner();
        [, $building] = $this->makePortfolio($owner, ['is_published' => true]);
        $unit = $this->makeUnit($building, ['unit_number' => 'PUB-1']);

        $this->getJson("/api/units/{$unit->id}")
            ->assertOk()
            ->assertJsonPath('data.unit_number', 'PUB-1');
    }

    public function test_the_catalog_does_not_expose_owner_contact_details(): void
    {
        $owner = $this->makeOwner(['email' => 'private-owner@example.com']);
        [$property] = $this->makePortfolio($owner, ['is_published' => true]);

        $listing = $this->getJson('/api/properties')->assertOk();
        $detail = $this->getJson("/api/properties/{$property->id}")->assertOk();

        $listing->assertJsonMissing(['email' => 'private-owner@example.com']);
        $detail->assertJsonMissing(['email' => 'private-owner@example.com']);
    }

    public function test_the_catalog_can_be_searched_and_filtered(): void
    {
        $owner = $this->makeOwner();
        [$cairo] = $this->makePortfolio($owner, ['name' => 'Zamalek Heights', 'city' => 'Cairo', 'is_published' => true]);
        [$alex] = $this->makePortfolio($owner, ['name' => 'Stanley Bay', 'city' => 'Alexandria', 'is_published' => true]);

        $ids = collect($this->getJson('/api/properties?search=Zamalek')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($cairo->id));
        $this->assertFalse($ids->contains($alex->id));

        $ids = collect($this->getJson('/api/properties?city=Alexandria')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($alex->id));
        $this->assertFalse($ids->contains($cairo->id));
    }
}
