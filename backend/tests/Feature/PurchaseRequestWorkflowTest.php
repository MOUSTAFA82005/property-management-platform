<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

class PurchaseRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    private User $owner;
    private User $customer;
    private Property $property;
    private Building $building;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->makeOwner();
        $this->customer = $this->makeCustomer();

        [$this->property, $this->building, $this->unit] = $this->makePortfolio(
            $this->owner,
            ['is_published' => true],
            ['status' => 'available']
        );
    }

    // -----------------------------------------------------------------
    // Customer side
    // -----------------------------------------------------------------

    public function test_a_customer_can_submit_a_request_for_an_available_public_unit(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/purchase-requests', [
            'unit_id' => $this->unit->id,
            'notes'   => 'Interested, can I view it this week?',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.customer_id', $this->customer->id);

        $this->assertDatabaseHas('purchase_requests', [
            'customer_id' => $this->customer->id,
            'unit_id'     => $this->unit->id,
            'status'      => 'pending',
        ]);
    }

    public function test_a_request_is_always_filed_against_the_authenticated_customer(): void
    {
        $someoneElse = $this->makeCustomer();

        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/purchase-requests', [
            'unit_id'     => $this->unit->id,
            // A forged customer_id must be ignored.
            'customer_id' => $someoneElse->id,
        ])->assertCreated();

        $this->assertSame($this->customer->id, $response->json('data.customer_id'));
    }

    public function test_a_customer_cannot_request_a_unit_in_an_unpublished_property(): void
    {
        $this->property->update(['is_published' => false]);

        Sanctum::actingAs($this->customer);

        $this->postJson('/api/purchase-requests', ['unit_id' => $this->unit->id])->assertNotFound();
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_a_customer_cannot_request_a_unit_that_is_not_available(): void
    {
        $this->unit->update(['status' => 'occupied']);

        Sanctum::actingAs($this->customer);

        $this->postJson('/api/purchase-requests', ['unit_id' => $this->unit->id])->assertStatus(422);
    }

    public function test_a_customer_cannot_open_two_requests_for_the_same_unit(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/purchase-requests', ['unit_id' => $this->unit->id])->assertCreated();
        $this->postJson('/api/purchase-requests', ['unit_id' => $this->unit->id])->assertStatus(422);

        $this->assertDatabaseCount('purchase_requests', 1);
    }

    public function test_an_owner_cannot_submit_a_purchase_request(): void
    {
        Sanctum::actingAs($this->owner);

        $this->postJson('/api/purchase-requests', ['unit_id' => $this->unit->id])->assertForbidden();
    }

    public function test_a_customer_only_lists_and_reads_their_own_requests(): void
    {
        $mine = $this->makePurchaseRequest($this->customer, $this->unit);

        $otherCustomer = $this->makeCustomer();
        $otherUnit = $this->makeUnit($this->building);
        $theirs = $this->makePurchaseRequest($otherCustomer, $otherUnit);

        Sanctum::actingAs($this->customer);

        $ids = collect($this->getJson('/api/purchase-requests')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($mine->id));
        $this->assertFalse($ids->contains($theirs->id));

        $this->getJson("/api/purchase-requests/{$mine->id}")->assertOk();
        $this->getJson("/api/purchase-requests/{$theirs->id}")->assertForbidden();
    }

    public function test_a_customer_can_cancel_their_own_pending_request_but_not_someone_elses(): void
    {
        $mine = $this->makePurchaseRequest($this->customer, $this->unit);
        $theirs = $this->makePurchaseRequest($this->makeCustomer(), $this->makeUnit($this->building));

        Sanctum::actingAs($this->customer);

        $this->deleteJson("/api/purchase-requests/{$theirs->id}")->assertForbidden();
        $this->assertSame('pending', $theirs->fresh()->status);

        $this->deleteJson("/api/purchase-requests/{$mine->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_a_closed_request_cannot_be_cancelled_again(): void
    {
        $rejected = $this->makePurchaseRequest($this->customer, $this->unit, ['status' => 'rejected']);

        Sanctum::actingAs($this->customer);

        $this->deleteJson("/api/purchase-requests/{$rejected->id}")->assertStatus(422);
        $this->assertSame('rejected', $rejected->fresh()->status);
    }

    // -----------------------------------------------------------------
    // Owner side
    // -----------------------------------------------------------------

    public function test_an_owner_sees_requests_raised_against_their_own_units(): void
    {
        $request = $this->makePurchaseRequest($this->customer, $this->unit);

        Sanctum::actingAs($this->owner);

        $ids = collect($this->getJson('/api/owner/purchase-requests')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($request->id));

        $this->getJson("/api/owner/purchase-requests/{$request->id}")
            ->assertOk()
            ->assertJsonPath('data.customer.id', $this->customer->id);
    }

    public function test_approving_a_request_reserves_the_unit(): void
    {
        $request = $this->makePurchaseRequest($this->customer, $this->unit);

        Sanctum::actingAs($this->owner);

        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame('reserved', $this->unit->fresh()->status);
    }

    public function test_rejecting_a_request_leaves_the_unit_untouched(): void
    {
        $request = $this->makePurchaseRequest($this->customer, $this->unit);

        Sanctum::actingAs($this->owner);

        $this->postJson("/api/owner/purchase-requests/{$request->id}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertSame('available', $this->unit->fresh()->status);
    }

    public function test_a_request_cannot_be_approved_twice(): void
    {
        $request = $this->makePurchaseRequest($this->customer, $this->unit);

        Sanctum::actingAs($this->owner);

        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")->assertOk();
        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")->assertStatus(422);
        $this->postJson("/api/owner/purchase-requests/{$request->id}/reject")->assertStatus(422);
    }

    public function test_a_request_cannot_be_approved_once_the_unit_is_taken(): void
    {
        $first = $this->makePurchaseRequest($this->customer, $this->unit);
        $second = $this->makePurchaseRequest($this->makeCustomer(), $this->unit);

        Sanctum::actingAs($this->owner);

        $this->postJson("/api/owner/purchase-requests/{$first->id}/approve")->assertOk();

        // The unit is reserved now, so the second approval must be refused
        // rather than producing two claims on one unit.
        $this->postJson("/api/owner/purchase-requests/{$second->id}/approve")->assertStatus(422);
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_cancelling_an_approved_request_releases_the_reservation(): void
    {
        $request = $this->makePurchaseRequest($this->customer, $this->unit);

        Sanctum::actingAs($this->owner);
        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")->assertOk();
        $this->assertSame('reserved', $this->unit->fresh()->status);

        Sanctum::actingAs($this->customer);
        $this->deleteJson("/api/purchase-requests/{$request->id}")->assertOk();

        // Leaving the unit reserved for a request nobody holds would be a
        // contradictory state.
        $this->assertSame('available', $this->unit->fresh()->status);
    }

    public function test_cancelling_one_of_two_approvals_keeps_the_unit_reserved(): void
    {
        $keep = $this->makePurchaseRequest($this->customer, $this->unit, ['status' => 'approved']);
        $drop = $this->makePurchaseRequest($this->makeCustomer(), $this->unit, ['status' => 'approved']);
        $this->unit->update(['status' => 'reserved']);

        Sanctum::actingAs($drop->customer);
        $this->deleteJson("/api/purchase-requests/{$drop->id}")->assertOk();

        $this->assertSame('reserved', $this->unit->fresh()->status);
        $this->assertSame('approved', $keep->fresh()->status);
    }

    public function test_purchase_request_endpoints_require_authentication(): void
    {
        $request = $this->makePurchaseRequest($this->customer, $this->unit);

        $this->getJson('/api/purchase-requests')->assertUnauthorized();
        $this->postJson('/api/purchase-requests', ['unit_id' => $this->unit->id])->assertUnauthorized();
        $this->getJson("/api/owner/purchase-requests/{$request->id}")->assertUnauthorized();
    }
}
