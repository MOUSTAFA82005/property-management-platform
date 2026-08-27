<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerPaymentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_only_payments_on_their_own_contracts(): void
    {
        [$customer, $ownPayment, $otherPayment] = $this->seedTwoCustomersWithPayments();

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/payments');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($ownPayment->id));
        $this->assertFalse($ids->contains($otherPayment->id));
    }

    public function test_customer_cannot_view_another_customers_payment(): void
    {
        [$customer, , $otherPayment] = $this->seedTwoCustomersWithPayments();

        Sanctum::actingAs($customer);

        $this->getJson("/api/payments/{$otherPayment->id}")->assertForbidden();
    }

    public function test_customer_can_view_their_own_payment(): void
    {
        [$customer, $ownPayment] = $this->seedTwoCustomersWithPayments();

        Sanctum::actingAs($customer);

        $this->getJson("/api/payments/{$ownPayment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ownPayment->id);
    }

    /**
     * @return array{0: User, 1: Payment, 2: Payment}
     */
    private function seedTwoCustomersWithPayments(): array
    {
        $owner = User::factory()->owner()->create();
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        $unitA = $this->createUnit($owner, '101');
        $unitB = $this->createUnit($owner, '102');

        $ownContract = Contract::query()->create([
            'user_id'           => $customer->id,
            'unit_id'           => $unitA->id,
            'start_date'        => now()->toDateString(),
            'end_date'          => now()->addYear()->toDateString(),
            'monthly_rent'      => 1500,
            'security_deposit'  => 3000,
            'status'            => 'active',
        ]);

        $otherContract = Contract::query()->create([
            'user_id'           => $otherCustomer->id,
            'unit_id'           => $unitB->id,
            'start_date'        => now()->toDateString(),
            'end_date'          => now()->addYear()->toDateString(),
            'monthly_rent'      => 1800,
            'security_deposit'  => 3600,
            'status'            => 'active',
        ]);

        $ownPayment = Payment::query()->create([
            'contract_id' => $ownContract->id,
            'amount'      => 1500,
            'due_date'    => now()->toDateString(),
            'status'      => 'pending',
            'reference'   => 'PAY-OWN',
        ]);

        $otherPayment = Payment::query()->create([
            'contract_id' => $otherContract->id,
            'amount'      => 1800,
            'due_date'    => now()->toDateString(),
            'status'      => 'pending',
            'reference'   => 'PAY-OTHER',
        ]);

        return [$customer, $ownPayment, $otherPayment];
    }

    private function createUnit(User $owner, string $unitNumber): Unit
    {
        $property = Property::query()->create([
            'owner_id'      => $owner->id,
            'name'          => 'Test Property '.$unitNumber,
            'address'       => '1 Test Street',
            'city'          => 'Cairo',
            'property_type' => 'residential',
            'status'        => 'active',
        ]);

        $building = Building::query()->create([
            'property_id' => $property->id,
            'name'        => 'Building '.$unitNumber,
        ]);

        return Unit::query()->create([
            'building_id'  => $building->id,
            'unit_number'  => $unitNumber,
            'floor'        => 1,
            'unit_type'    => 'apartment',
            'monthly_rent' => 1500,
            'status'       => 'occupied',
        ]);
    }
}
