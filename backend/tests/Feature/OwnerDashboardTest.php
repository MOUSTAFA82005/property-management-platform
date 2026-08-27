<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    public function test_the_dashboard_reports_only_the_authenticated_owners_figures(): void
    {
        // Owner A: 2 properties (1 published), 3 units, 1 active contract,
        // 2 payments (one paid, one overdue), 1 pending request.
        $ownerA = $this->makeOwner();
        $customerA = $this->makeCustomer();

        [$propA1, $buildingA] = $this->makePortfolio($ownerA, ['is_published' => true], ['status' => 'occupied']);
        $this->makeProperty($ownerA, ['is_published' => false]);
        $availableA = $this->makeUnit($buildingA, ['status' => 'available']);
        $this->makeUnit($buildingA, ['status' => 'reserved']);

        $occupiedA = $propA1->units()->where('status', 'occupied')->firstOrFail();
        $contractA = $this->makeContract($customerA, $occupiedA, ['status' => 'active', 'monthly_rent' => 15000]);
        $this->makePayment($contractA, ['status' => 'paid', 'amount' => 15000, 'paid_date' => now()->toDateString()]);
        $this->makePayment($contractA, ['status' => 'overdue', 'amount' => 15000]);
        $this->makePurchaseRequest($customerA, $availableA);

        // Owner B: a completely separate, larger portfolio.
        $ownerB = $this->makeOwner();
        $customerB = $this->makeCustomer();
        [, $buildingB, $unitB] = $this->makePortfolio($ownerB, ['is_published' => true], ['status' => 'occupied']);
        $this->makeUnit($buildingB, ['status' => 'available']);
        $contractB = $this->makeContract($customerB, $unitB, ['status' => 'active', 'monthly_rent' => 90000]);
        $this->makePayment($contractB, ['status' => 'paid', 'amount' => 90000, 'paid_date' => now()->toDateString()]);
        $this->makePayment($contractB, ['status' => 'paid', 'amount' => 90000, 'paid_date' => now()->toDateString()]);

        // ---- Owner A ------------------------------------------------
        Sanctum::actingAs($ownerA);
        $a = $this->getJson('/api/owner/dashboard')->assertOk()->json('data');

        $this->assertSame(2, $a['properties']['total']);
        $this->assertSame(1, $a['properties']['published']);
        $this->assertSame(1, $a['properties']['unpublished']);
        $this->assertSame(3, $a['units']['total']);
        $this->assertSame(1, $a['units']['available']);
        $this->assertSame(1, $a['units']['occupied']);
        $this->assertSame(1, $a['units']['reserved']);
        $this->assertSame(1, $a['contracts']['active']);
        $this->assertSame(1, $a['customers']['total']);
        $this->assertSame(1, $a['purchase_requests']['pending']);
        $this->assertSame(2, $a['payments']['total']);
        $this->assertEqualsWithDelta(15000, $a['payments']['collected_amount'], 0.001);
        $this->assertEqualsWithDelta(15000, $a['payments']['overdue_amount'], 0.001);
        $this->assertEqualsWithDelta(15000, $a['monthly_expected_rent'], 0.001);

        // ---- Owner B ------------------------------------------------
        Sanctum::actingAs($ownerB);
        $b = $this->getJson('/api/owner/dashboard')->assertOk()->json('data');

        $this->assertSame(1, $b['properties']['total']);
        $this->assertSame(2, $b['units']['total']);
        $this->assertSame(2, $b['payments']['total']);
        $this->assertEqualsWithDelta(180000, $b['payments']['collected_amount'], 0.001);
        $this->assertEqualsWithDelta(90000, $b['monthly_expected_rent'], 0.001);
        $this->assertSame(0, $b['purchase_requests']['total']);

        // Neither owner's totals contain any part of the other's.
        $this->assertNotEquals($a['payments']['collected_amount'], $b['payments']['collected_amount']);
    }

    public function test_the_property_overview_lists_only_the_owners_properties(): void
    {
        $ownerA = $this->makeOwner();
        $ownerB = $this->makeOwner();

        [$mine] = $this->makePortfolio($ownerA, ['name' => 'Mine']);
        [$theirs] = $this->makePortfolio($ownerB, ['name' => 'Theirs']);

        Sanctum::actingAs($ownerA);

        $overview = collect($this->getJson('/api/owner/dashboard')->assertOk()->json('data.property_overview'));

        $this->assertTrue($overview->contains('id', $mine->id));
        $this->assertFalse($overview->contains('id', $theirs->id));
        $this->assertSame(1, $overview->firstWhere('id', $mine->id)['units']['total']);
    }

    public function test_recent_lists_are_capped_and_scoped(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, $building, $unit] = $this->makePortfolio($owner);
        $contract = $this->makeContract($customer, $unit);

        for ($i = 0; $i < 8; $i++) {
            $this->makePayment($contract, ['due_date' => now()->subDays($i)->toDateString()]);
            $this->makePurchaseRequest($customer, $this->makeUnit($building));
        }

        // Another owner's activity must not appear.
        $otherOwner = $this->makeOwner();
        [, , $otherUnit] = $this->makePortfolio($otherOwner);
        $otherContract = $this->makeContract($this->makeCustomer(), $otherUnit);
        $otherPayment = $this->makePayment($otherContract, ['reference' => 'FOREIGN-REF']);

        Sanctum::actingAs($owner);
        $data = $this->getJson('/api/owner/dashboard')->assertOk()->json('data');

        $this->assertCount(5, $data['recent_payments']);
        $this->assertCount(5, $data['recent_purchase_requests']);

        $refs = collect($data['recent_payments'])->pluck('reference');
        $this->assertFalse($refs->contains('FOREIGN-REF'));
        $this->assertNotContains($otherPayment->id, collect($data['recent_payments'])->pluck('id')->all());
    }

    public function test_an_owner_with_nothing_gets_zeroes_rather_than_an_error(): void
    {
        Sanctum::actingAs($this->makeOwner());

        $data = $this->getJson('/api/owner/dashboard')->assertOk()->json('data');

        $this->assertSame(0, $data['properties']['total']);
        $this->assertSame(0, $data['units']['total']);
        $this->assertSame(0, $data['payments']['total']);
        $this->assertEqualsWithDelta(0, $data['payments']['collected_amount'], 0.001);
        $this->assertEqualsWithDelta(0, $data['monthly_expected_rent'], 0.001);
        $this->assertSame([], $data['property_overview']);
    }

    public function test_the_dashboard_requires_an_authenticated_owner(): void
    {
        $this->getJson('/api/owner/dashboard')->assertUnauthorized();

        Sanctum::actingAs($this->makeCustomer());
        $this->getJson('/api/owner/dashboard')->assertForbidden();
    }
}
