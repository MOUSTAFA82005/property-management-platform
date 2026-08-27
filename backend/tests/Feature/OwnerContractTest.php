<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Regression coverage for the owner contract endpoints.
 *
 * Two defects motivated these: every query eager-loaded a `customer`
 * relationship the Contract model does not define, and the write side
 * validated `customer_id` while the column and $fillable are `user_id`,
 * so mass assignment silently dropped it and creation failed outright.
 */
class OwnerContractTest extends TestCase
{
    use RefreshDatabase;

    private function makeUnitFor(User $owner, string $unitNumber = '101'): Unit
    {
        $property = Property::query()->create([
            'owner_id'      => $owner->id,
            'name'          => 'Property '.$unitNumber,
            'address'       => '1 Test Street',
            'city'          => 'Cairo',
            'property_type' => 'Apartment Building',
            'status'        => 'active',
        ]);

        $building = Building::query()->create([
            'property_id'  => $property->id,
            'name'         => 'Building '.$unitNumber,
            'floors_count' => 3,
        ]);

        return Unit::query()->create([
            'building_id'  => $building->id,
            'unit_number'  => $unitNumber,
            'floor'        => 1,
            'unit_type'    => 'Apartment',
            'monthly_rent' => 12000,
            'status'       => 'available',
        ]);
    }

    private function contractPayload(User $customer, Unit $unit): array
    {
        return [
            'user_id'          => $customer->id,
            'unit_id'          => $unit->id,
            'start_date'       => now()->toDateString(),
            'end_date'         => now()->addYear()->toDateString(),
            'monthly_rent'     => 12000,
            'security_deposit' => 24000,
            'status'           => 'active',
        ];
    }

    public function test_owner_can_create_a_contract_and_the_unit_becomes_occupied(): void
    {
        $owner    = User::factory()->owner()->create();
        $customer = User::factory()->customer()->create();
        $unit     = $this->makeUnitFor($owner);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/owner/contracts', $this->contractPayload($customer, $unit));

        $response->assertCreated()
            ->assertJsonPath('data.user_id', $customer->id)
            ->assertJsonPath('data.user.name', $customer->name);

        $this->assertDatabaseHas('contracts', [
            'user_id' => $customer->id,
            'unit_id' => $unit->id,
        ]);

        $this->assertSame('occupied', $unit->fresh()->status);
    }

    public function test_owner_cannot_create_a_contract_on_another_owners_unit(): void
    {
        $owner      = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $customer   = User::factory()->customer()->create();
        $foreignUnit = $this->makeUnitFor($otherOwner, '202');

        Sanctum::actingAs($owner);

        $this->postJson('/api/owner/contracts', $this->contractPayload($customer, $foreignUnit))
            ->assertForbidden();

        $this->assertDatabaseCount('contracts', 0);
    }

    public function test_a_contract_cannot_be_assigned_to_an_owner_account(): void
    {
        $owner      = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $unit       = $this->makeUnitFor($owner);

        Sanctum::actingAs($owner);

        $this->postJson('/api/owner/contracts', $this->contractPayload($otherOwner, $unit))
            ->assertStatus(422);
    }

    public function test_owner_index_lists_only_contracts_on_their_own_properties(): void
    {
        $owner      = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $customer   = User::factory()->customer()->create();

        $ownContract = Contract::query()->create(
            $this->contractPayload($customer, $this->makeUnitFor($owner, '301'))
        );
        $foreignContract = Contract::query()->create(
            $this->contractPayload($customer, $this->makeUnitFor($otherOwner, '401'))
        );

        Sanctum::actingAs($owner);

        $ids = collect($this->getJson('/api/owner/contracts')->assertOk()->json('data'))
            ->pluck('id');

        $this->assertTrue($ids->contains($ownContract->id));
        $this->assertFalse($ids->contains($foreignContract->id));
    }

    public function test_owner_cannot_view_another_owners_contract(): void
    {
        $owner      = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $customer   = User::factory()->customer()->create();

        $foreignContract = Contract::query()->create(
            $this->contractPayload($customer, $this->makeUnitFor($otherOwner, '501'))
        );

        Sanctum::actingAs($owner);

        $this->getJson("/api/owner/contracts/{$foreignContract->id}")->assertForbidden();
    }

    public function test_customer_cannot_reach_the_owner_contract_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->postJson('/api/owner/contracts', [])->assertForbidden();
    }
}
