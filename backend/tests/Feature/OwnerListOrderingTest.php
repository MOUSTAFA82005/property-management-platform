<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * Owner lists read in id order.
 *
 * They used to use `latest()`, which orders by created_at — and the seeder
 * writes a whole portfolio inside the same second, so the tie-break was
 * arbitrary and the id column looked shuffled. Anything created afterwards
 * also jumped to the top of the list.
 *
 * Payments are the deliberate exception: they stay ordered by due date,
 * because a rent schedule reads by when money is owed. Id is only their
 * tie-break, so equal due dates stop shuffling.
 */
class OwnerListOrderingTest extends TestCase
{
    use RefreshDatabase, BuildsPortfolios;

    /** @return array<int, int> */
    private function ids(string $path): array
    {
        return collect($this->getJson($path)->json('data'))->pluck('id')->all();
    }

    private function assertAscending(string $path): void
    {
        $ids = $this->ids($path);

        $this->assertNotEmpty($ids, "{$path} returned nothing to order.");

        $sorted = $ids;
        sort($sorted);

        $this->assertSame($sorted, $ids, "{$path} is not in ascending id order.");
    }

    public function test_every_owner_list_reads_in_id_order(): void
    {
        $owner    = $this->makeOwner();
        $customer = $this->makeCustomer();

        // Three portfolios, so each list has more than one row to order.
        foreach (range(1, 3) as $n) {
            [, , $unit] = $this->makePortfolio($owner);

            $contract = $this->makeContract($customer, $unit);
            $this->makePayment($contract);
            $this->makePurchaseRequest($customer, $unit);
        }

        Sanctum::actingAs($owner);

        foreach ([
            '/api/owner/properties',
            '/api/owner/buildings',
            '/api/owner/units',
            '/api/owner/contracts',
            '/api/owner/purchase-requests',
            '/api/owner/customers',
        ] as $path) {
            $this->assertAscending($path);
        }
    }

    public function test_a_newly_created_record_lands_at_the_end_not_the_top(): void
    {
        $owner = $this->makeOwner();

        $this->makeProperty($owner);
        $this->makeProperty($owner);

        Sanctum::actingAs($owner);

        $before = $this->ids('/api/owner/properties');

        $created = $this->postJson('/api/owner/properties', [
            'name'          => 'Newest Property',
            'address'       => '9 Later Street',
            'city'          => 'Cairo',
            'property_type' => 'Apartment Building',
        ])->assertCreated()->json('data.id');

        $after = $this->ids('/api/owner/properties');

        $this->assertSame([...$before, $created], $after);
    }

    public function test_payments_stay_ordered_by_due_date(): void
    {
        $owner    = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);

        $contract = $this->makeContract($customer, $unit);

        // Created oldest-first, so id order and due-date order disagree.
        $this->makePayment($contract, ['due_date' => now()->subMonths(2)->toDateString()]);
        $this->makePayment($contract, ['due_date' => now()->toDateString()]);
        $this->makePayment($contract, ['due_date' => now()->subMonth()->toDateString()]);

        Sanctum::actingAs($owner);

        $dueDates = collect($this->getJson('/api/owner/payments')->json('data'))
            ->pluck('due_date')
            ->all();

        $sorted = $dueDates;
        rsort($sorted);

        $this->assertSame($sorted, $dueDates, 'Payments should still read newest due date first.');
    }
}
