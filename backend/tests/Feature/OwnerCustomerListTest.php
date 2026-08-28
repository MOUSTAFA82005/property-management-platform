<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * GET /api/owner/customers used to return every customer in the database.
 * It must now return exactly those connected to the owner's own properties,
 * including customers who also deal with a different owner.
 */
class OwnerCustomerListTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    public function test_the_list_contains_only_customers_connected_to_this_owner(): void
    {
        $ownerA = $this->makeOwner();
        $ownerB = $this->makeOwner();

        [, $buildingA] = $this->makePortfolio($ownerA);
        [, $buildingB] = $this->makePortfolio($ownerB);

        // Connected to A by a contract.
        $tenantOfA = $this->makeCustomer(['name' => 'Tenant of A']);
        $this->makeContract($tenantOfA, $this->makeUnit($buildingA));

        // Connected to A only by a purchase request.
        $enquirerOfA = $this->makeCustomer(['name' => 'Enquirer of A']);
        $this->makePurchaseRequest($enquirerOfA, $this->makeUnit($buildingA));

        // Connected to B only.
        $tenantOfB = $this->makeCustomer(['name' => 'Tenant of B']);
        $this->makeContract($tenantOfB, $this->makeUnit($buildingB));

        // Connected to both.
        $shared = $this->makeCustomer(['name' => 'Shared Customer']);
        $this->makeContract($shared, $this->makeUnit($buildingA));
        $this->makePurchaseRequest($shared, $this->makeUnit($buildingB));

        // Connected to nobody — the case the old implementation leaked.
        $stranger = $this->makeCustomer(['name' => 'Unrelated Stranger']);

        Sanctum::actingAs($ownerA);
        $namesForA = collect($this->getJson('/api/owner/customers')->assertOk()->json('data'))->pluck('name');

        $this->assertTrue($namesForA->contains('Tenant of A'));
        $this->assertTrue($namesForA->contains('Enquirer of A'));
        $this->assertTrue($namesForA->contains('Shared Customer'));
        $this->assertFalse($namesForA->contains('Tenant of B'));
        $this->assertFalse($namesForA->contains('Unrelated Stranger'));
        $this->assertCount(3, $namesForA);

        Sanctum::actingAs($ownerB);
        $namesForB = collect($this->getJson('/api/owner/customers')->assertOk()->json('data'))->pluck('name');

        $this->assertTrue($namesForB->contains('Tenant of B'));
        $this->assertTrue($namesForB->contains('Shared Customer'));
        $this->assertFalse($namesForB->contains('Tenant of A'));
        $this->assertFalse($namesForB->contains('Enquirer of A'));
        $this->assertFalse($namesForB->contains('Unrelated Stranger'));
        $this->assertCount(2, $namesForB);
    }

    public function test_owners_are_never_listed_as_customers(): void
    {
        $ownerA = $this->makeOwner();
        [, $building] = $this->makePortfolio($ownerA);
        $this->makeContract($this->makeCustomer(), $this->makeUnit($building));

        Sanctum::actingAs($ownerA);

        $roles = collect($this->getJson('/api/owner/customers')->assertOk()->json('data'))->pluck('role')->unique();

        $this->assertSame(['customer'], $roles->values()->all());
    }

    public function test_an_unrelated_customer_cannot_be_read_by_id(): void
    {
        $ownerA = $this->makeOwner();
        $ownerB = $this->makeOwner();

        [, $buildingA] = $this->makePortfolio($ownerA);
        [, $buildingB] = $this->makePortfolio($ownerB);

        $mine = $this->makeCustomer();
        $this->makeContract($mine, $this->makeUnit($buildingA));

        $theirs = $this->makeCustomer();
        $this->makeContract($theirs, $this->makeUnit($buildingB));

        $stranger = $this->makeCustomer();

        Sanctum::actingAs($ownerA);

        $this->getJson("/api/owner/customers/{$mine->id}")->assertOk()->assertJsonPath('data.id', $mine->id);
        $this->getJson("/api/owner/customers/{$theirs->id}")->assertNotFound();
        $this->getJson("/api/owner/customers/{$stranger->id}")->assertNotFound();
        $this->getJson("/api/owner/customers/{$ownerB->id}")->assertNotFound();
    }

    public function test_a_customer_detail_only_shows_records_from_this_owner(): void
    {
        $ownerA = $this->makeOwner();
        $ownerB = $this->makeOwner();

        [, $buildingA] = $this->makePortfolio($ownerA);
        [, $buildingB] = $this->makePortfolio($ownerB);

        $shared = $this->makeCustomer();
        $contractWithA = $this->makeContract($shared, $this->makeUnit($buildingA));
        $contractWithB = $this->makeContract($shared, $this->makeUnit($buildingB));

        Sanctum::actingAs($ownerA);

        $contracts = collect(
            $this->getJson("/api/owner/customers/{$shared->id}")->assertOk()->json('data.contracts')
        )->pluck('id');

        $this->assertTrue($contracts->contains($contractWithA->id));
        $this->assertFalse($contracts->contains($contractWithB->id));
    }
}
