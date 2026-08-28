<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PurchaseRequest;
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

    // =================================================================
    // Approved request → contract
    // =================================================================

    public function test_an_approved_purchase_request_can_be_turned_into_a_contract(): void
    {
        $owner    = User::factory()->owner()->create();
        $customer = User::factory()->customer()->create();
        $unit     = $this->makeUnitFor($owner, '601');

        $request = PurchaseRequest::query()->create([
            'customer_id' => $customer->id,
            'unit_id'     => $unit->id,
            'status'      => 'pending',
        ]);

        Sanctum::actingAs($owner);

        // Approving reserves the unit — the contract has to be creatable
        // from that state or the approval leads nowhere.
        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")->assertOk();
        $this->assertSame('reserved', $unit->fresh()->status);

        $this->postJson('/api/owner/contracts', $this->contractPayload($customer, $unit))
            ->assertCreated();

        $this->assertSame('occupied', $unit->fresh()->status);
    }

    public function test_a_unit_reserved_for_someone_else_cannot_be_let_to_a_different_customer(): void
    {
        $owner     = User::factory()->owner()->create();
        $holder    = User::factory()->customer()->create();
        $outsider  = User::factory()->customer()->create();
        $unit      = $this->makeUnitFor($owner, '602');

        $request = PurchaseRequest::query()->create([
            'customer_id' => $holder->id,
            'unit_id'     => $unit->id,
            'status'      => 'pending',
        ]);

        Sanctum::actingAs($owner);
        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")->assertOk();

        $this->postJson('/api/owner/contracts', $this->contractPayload($outsider, $unit))
            ->assertStatus(422);

        $this->assertDatabaseCount('contracts', 0);
        $this->assertSame('reserved', $unit->fresh()->status);
    }

    public function test_an_occupied_unit_still_cannot_be_let_again(): void
    {
        $owner    = User::factory()->owner()->create();
        $customer = User::factory()->customer()->create();
        $unit     = $this->makeUnitFor($owner, '603');
        $unit->update(['status' => 'occupied']);

        Sanctum::actingAs($owner);

        $this->postJson('/api/owner/contracts', $this->contractPayload($customer, $unit))
            ->assertStatus(422);
    }

    // =================================================================
    // Deletion
    // =================================================================

    public function test_a_contract_with_payments_is_refused_rather_than_deleted(): void
    {
        $owner    = User::factory()->owner()->create();
        $customer = User::factory()->customer()->create();
        $unit     = $this->makeUnitFor($owner, '701');

        $contract = Contract::query()->create($this->contractPayload($customer, $unit));

        Payment::query()->create([
            'contract_id' => $contract->id,
            'amount'      => 12000,
            'due_date'    => now()->toDateString(),
            'status'      => 'pending',
            'reference'   => 'REF-DELETE-GUARD',
        ]);

        Sanctum::actingAs($owner);

        // Payment history must survive: a 409 with a message, never a
        // database-level failure and never a silent loss of the payments.
        $this->deleteJson("/api/owner/contracts/{$contract->id}")->assertStatus(409);

        $this->assertDatabaseHas('contracts', ['id' => $contract->id]);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_a_contract_without_payments_can_be_deleted_and_frees_the_unit(): void
    {
        $owner    = User::factory()->owner()->create();
        $customer = User::factory()->customer()->create();
        $unit     = $this->makeUnitFor($owner, '702');
        $unit->update(['status' => 'occupied']);

        $contract = Contract::query()->create($this->contractPayload($customer, $unit));

        Sanctum::actingAs($owner);

        $this->deleteJson("/api/owner/contracts/{$contract->id}")->assertNoContent();

        $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
        $this->assertSame('available', $unit->fresh()->status);
    }
}
