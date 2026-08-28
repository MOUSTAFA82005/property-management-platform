<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * One owner must never reach another owner's records — not by listing them,
 * and not by guessing an id.
 */
class OwnerIsolationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    private User $ownerA;
    private User $ownerB;
    private User $customer;

    /** @var array<string, mixed> */
    private array $b = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->ownerA = $this->makeOwner();
        $this->ownerB = $this->makeOwner();
        $this->customer = $this->makeCustomer();

        [$property, $building, $unit] = $this->makePortfolio($this->ownerB, ['name' => 'Owner B Tower']);
        $contract = $this->makeContract($this->customer, $unit, ['status' => 'active']);

        $this->b = [
            'property' => $property,
            'building' => $building,
            'unit'     => $unit,
            'contract' => $contract,
            'payment'  => $this->makePayment($contract),
            'request'  => $this->makePurchaseRequest($this->customer, $unit),
        ];

        // Owner A gets a portfolio of their own so the lists are not empty
        // for the wrong reason.
        $this->makePortfolio($this->ownerA, ['name' => 'Owner A Tower']);
    }

    // -----------------------------------------------------------------
    // Lists never contain the other owner's records
    // -----------------------------------------------------------------

    public function test_owner_lists_exclude_the_other_owners_records(): void
    {
        Sanctum::actingAs($this->ownerA);

        $cases = [
            '/api/owner/properties'        => $this->b['property']->id,
            '/api/owner/buildings'         => $this->b['building']->id,
            '/api/owner/units'             => $this->b['unit']->id,
            '/api/owner/contracts'         => $this->b['contract']->id,
            '/api/owner/payments'          => $this->b['payment']->id,
            '/api/owner/purchase-requests' => $this->b['request']->id,
        ];

        foreach ($cases as $endpoint => $foreignId) {
            $ids = collect($this->getJson($endpoint)->assertOk()->json('data'))->pluck('id');

            $this->assertFalse(
                $ids->contains($foreignId),
                "{$endpoint} leaked a record belonging to another owner."
            );
        }
    }

    public function test_owner_lists_do_contain_their_own_records(): void
    {
        Sanctum::actingAs($this->ownerB);

        $cases = [
            '/api/owner/properties'        => $this->b['property']->id,
            '/api/owner/buildings'         => $this->b['building']->id,
            '/api/owner/units'             => $this->b['unit']->id,
            '/api/owner/contracts'         => $this->b['contract']->id,
            '/api/owner/payments'          => $this->b['payment']->id,
            '/api/owner/purchase-requests' => $this->b['request']->id,
        ];

        foreach ($cases as $endpoint => $ownId) {
            $ids = collect($this->getJson($endpoint)->assertOk()->json('data'))->pluck('id');

            $this->assertTrue($ids->contains($ownId), "{$endpoint} did not return the owner's own record.");
        }
    }

    // -----------------------------------------------------------------
    // Direct id access is refused
    // -----------------------------------------------------------------

    public function test_owner_cannot_read_another_owners_records_by_id(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->getJson("/api/owner/properties/{$this->b['property']->id}")->assertForbidden();
        $this->getJson("/api/owner/buildings/{$this->b['building']->id}")->assertForbidden();
        $this->getJson("/api/owner/units/{$this->b['unit']->id}")->assertForbidden();
        $this->getJson("/api/owner/contracts/{$this->b['contract']->id}")->assertForbidden();
        $this->getJson("/api/owner/payments/{$this->b['payment']->id}")->assertForbidden();
        $this->getJson("/api/owner/purchase-requests/{$this->b['request']->id}")->assertForbidden();
    }

    public function test_owner_cannot_update_another_owners_records(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->putJson("/api/owner/properties/{$this->b['property']->id}", ['name' => 'Hijacked'])->assertForbidden();
        $this->putJson("/api/owner/buildings/{$this->b['building']->id}", ['name' => 'Hijacked'])->assertForbidden();
        $this->putJson("/api/owner/units/{$this->b['unit']->id}", ['monthly_rent' => 1])->assertForbidden();
        $this->putJson("/api/owner/contracts/{$this->b['contract']->id}", ['monthly_rent' => 1])->assertForbidden();
        $this->putJson("/api/owner/payments/{$this->b['payment']->id}", ['amount' => 1])->assertForbidden();

        $this->assertSame('Owner B Tower', $this->b['property']->fresh()->name);
        $this->assertEquals(12000, $this->b['payment']->fresh()->amount);
    }

    public function test_owner_cannot_delete_another_owners_records(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->deleteJson("/api/owner/properties/{$this->b['property']->id}")->assertForbidden();
        $this->deleteJson("/api/owner/buildings/{$this->b['building']->id}")->assertForbidden();
        $this->deleteJson("/api/owner/units/{$this->b['unit']->id}")->assertForbidden();
        $this->deleteJson("/api/owner/contracts/{$this->b['contract']->id}")->assertForbidden();
        $this->deleteJson("/api/owner/payments/{$this->b['payment']->id}")->assertForbidden();

        $this->assertDatabaseHas('properties', ['id' => $this->b['property']->id]);
        $this->assertDatabaseHas('payments', ['id' => $this->b['payment']->id]);
        $this->assertDatabaseHas('contracts', ['id' => $this->b['contract']->id]);
    }

    public function test_owner_cannot_publish_or_unpublish_another_owners_property(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->postJson("/api/owner/properties/{$this->b['property']->id}/publish")->assertForbidden();
        $this->postJson("/api/owner/properties/{$this->b['property']->id}/unpublish")->assertForbidden();

        $this->assertTrue($this->b['property']->fresh()->is_published);
    }

    // -----------------------------------------------------------------
    // Creating records against another owner's parents
    // -----------------------------------------------------------------

    public function test_owner_cannot_add_a_building_to_another_owners_property(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->postJson('/api/owner/buildings', [
            'property_id' => $this->b['property']->id,
            'name'        => 'Trespassing Block',
        ])->assertForbidden();

        $this->assertDatabaseMissing('buildings', ['name' => 'Trespassing Block']);
    }

    public function test_owner_cannot_add_a_unit_to_another_owners_building(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->postJson('/api/owner/units', [
            'building_id'  => $this->b['building']->id,
            'unit_number'  => 'TRESPASS-1',
            'unit_type'    => 'Apartment',
            'monthly_rent' => 5000,
        ])->assertForbidden();

        $this->assertDatabaseMissing('units', ['unit_number' => 'TRESPASS-1']);
    }

    public function test_owner_cannot_raise_a_payment_against_another_owners_contract(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->postJson('/api/owner/payments', [
            'contract_id' => $this->b['contract']->id,
            'amount'      => 999,
            'due_date'    => now()->toDateString(),
            'status'      => 'pending',
            'reference'   => 'TRESPASS-PAY',
        ])->assertForbidden();

        $this->assertDatabaseMissing('payments', ['reference' => 'TRESPASS-PAY']);
    }

    public function test_owner_cannot_move_their_own_payment_onto_another_owners_contract(): void
    {
        [, , $unitA] = $this->makePortfolio($this->ownerA);
        $ownContract = $this->makeContract($this->customer, $unitA);
        $ownPayment = $this->makePayment($ownContract);

        Sanctum::actingAs($this->ownerA);

        $this->putJson("/api/owner/payments/{$ownPayment->id}", [
            'contract_id' => $this->b['contract']->id,
        ])->assertForbidden();

        $this->assertSame($ownContract->id, $ownPayment->fresh()->contract_id);
    }

    public function test_owner_cannot_move_a_building_into_another_owners_property(): void
    {
        [$propertyA] = $this->makePortfolio($this->ownerA);
        $buildingA = $this->makeBuilding($propertyA);

        Sanctum::actingAs($this->ownerA);

        $this->putJson("/api/owner/buildings/{$buildingA->id}", [
            'property_id' => $this->b['property']->id,
        ])->assertForbidden();

        $this->assertSame($propertyA->id, $buildingA->fresh()->property_id);
    }

    public function test_owner_cannot_approve_or_reject_another_owners_purchase_request(): void
    {
        Sanctum::actingAs($this->ownerA);

        $this->postJson("/api/owner/purchase-requests/{$this->b['request']->id}/approve")->assertForbidden();
        $this->postJson("/api/owner/purchase-requests/{$this->b['request']->id}/reject")->assertForbidden();

        $this->assertSame('pending', $this->b['request']->fresh()->status);
    }

    // -----------------------------------------------------------------
    // A new property always belongs to the caller
    // -----------------------------------------------------------------

    public function test_a_created_property_belongs_to_the_authenticated_owner_regardless_of_the_payload(): void
    {
        Sanctum::actingAs($this->ownerA);

        $response = $this->postJson('/api/owner/properties', [
            'name'          => 'My New Tower',
            'address'       => '9 New Street',
            'city'          => 'Giza',
            'property_type' => 'Apartment Building',
            // A forged owner_id must be ignored, not honoured.
            'owner_id'      => $this->ownerB->id,
        ])->assertCreated();

        $this->assertSame($this->ownerA->id, $response->json('data.owner_id'));
        $this->assertDatabaseHas('properties', [
            'name'     => 'My New Tower',
            'owner_id' => $this->ownerA->id,
        ]);
    }
}
