<?php

namespace Tests\Feature;

use App\Http\Resources\PropertyResource;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Covers the property/building/unit schema as it exists today.
 *
 * The original version of this file exercised POST/PUT/DELETE /api/properties
 * and /api/units against a `manager_id` column. Both were replaced: property
 * writes now live under /api/owner/*, and `manager_id` was renamed to
 * `owner_id` by the 2026_08_25_100001 migration. These tests target the
 * current architecture instead.
 */
class PropertyAndUnitTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwnerWithUnit(string $unitNumber = '101'): array
    {
        $owner = User::factory()->owner()->create();

        $property = Property::query()->create([
            'owner_id'      => $owner->id,
            'name'          => 'Nile View Tower',
            'address'       => '12 Corniche Street',
            'city'          => 'Cairo',
            'description'   => 'Riverside apartments',
            'property_type' => 'Apartment Building',
            'status'        => 'active',
        ]);

        $building = Building::query()->create([
            'property_id'  => $property->id,
            'name'         => 'Tower A',
            'floors_count' => 8,
        ]);

        $unit = Unit::query()->create([
            'building_id'  => $building->id,
            'unit_number'  => $unitNumber,
            'floor'        => 1,
            'unit_type'    => '2 BHK',
            'area'         => 120.5,
            'bedrooms'     => 2,
            'bathrooms'    => 2,
            'monthly_rent' => 15000,
            'status'       => 'available',
        ]);

        return [$owner, $property, $building, $unit];
    }

    // ---------------------------------------------------------------
    // Schema / relationships
    // ---------------------------------------------------------------

    public function test_a_property_belongs_to_its_owner_via_owner_id(): void
    {
        [$owner, $property] = $this->makeOwnerWithUnit();

        $this->assertSame($owner->id, $property->owner_id);
        $this->assertSame($owner->id, $property->owner->id);
        $this->assertTrue($owner->properties->contains($property));
    }

    public function test_properties_reach_units_through_buildings(): void
    {
        [, $property, $building, $unit] = $this->makeOwnerWithUnit();

        $this->assertTrue($property->buildings->contains($building));
        $this->assertTrue($property->units()->get()->contains($unit));
        $this->assertSame($property->id, $unit->property->id);
    }

    public function test_unit_payments_resolve_through_contracts(): void
    {
        [, , , $unit] = $this->makeOwnerWithUnit();
        $customer = User::factory()->customer()->create();

        $contract = Contract::query()->create([
            'user_id'          => $customer->id,
            'unit_id'          => $unit->id,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->addYear()->toDateString(),
            'monthly_rent'     => 15000,
            'security_deposit' => 30000,
            'status'           => 'active',
        ]);

        $payment = Payment::query()->create([
            'contract_id' => $contract->id,
            'amount'      => 15000,
            'due_date'    => now()->toDateString(),
            'status'      => 'pending',
            'reference'   => 'PAY-UNIT-1',
        ]);

        // payments has no unit_id column — this must go through contracts.
        $this->assertTrue($unit->payments()->get()->contains($payment));
        $this->assertTrue($customer->payments()->get()->contains($payment));
    }

    // ---------------------------------------------------------------
    // Resource shape
    // ---------------------------------------------------------------

    public function test_property_resource_exposes_owner_not_manager(): void
    {
        [$owner, $property] = $this->makeOwnerWithUnit();

        $payload = (new PropertyResource($property->load('owner')))
            ->toArray(Request::create('/api/properties'));

        $this->assertArrayNotHasKey('manager_id', $payload);
        $this->assertArrayNotHasKey('manager', $payload);

        $this->assertSame($owner->id, $payload['owner_id']);
        $this->assertSame($owner->name, $payload['owner']['name']);
        $this->assertSame(1, $payload['units_count']);
        $this->assertSame(1, $payload['available_units_count']);
        $this->assertSame(15000.0, $payload['from_price']);
        $this->assertFalse($payload['is_published']);
    }

    // ---------------------------------------------------------------
    // Route surface
    // ---------------------------------------------------------------

    public function test_owner_property_and_unit_routes_require_authentication(): void
    {
        $this->getJson('/api/owner/properties')->assertUnauthorized();
        $this->postJson('/api/owner/properties', [])->assertUnauthorized();
        $this->getJson('/api/owner/units')->assertUnauthorized();
        $this->getJson('/api/owner/buildings')->assertUnauthorized();
    }

    public function test_property_writes_are_no_longer_exposed_on_the_public_path(): void
    {
        // Writes moved to /api/owner/properties; the old unscoped routes are gone.
        $this->postJson('/api/properties', [])->assertStatus(405);
        $this->postJson('/api/units', [])->assertNotFound();
    }
}
