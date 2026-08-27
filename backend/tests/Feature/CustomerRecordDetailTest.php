<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * A customer must be able to tell which unit their own contract or payment
 * refers to. These endpoints previously returned no unit at all, so the
 * account pages had nothing to render.
 */
class CustomerRecordDetailTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    public function test_a_customers_contracts_carry_their_unit_and_property(): void
    {
        $owner = $this->makeOwner();
        [$property, $building] = $this->makePortfolio($owner, ['name' => 'Nile View Residences']);
        $unit = $this->makeUnit($building, ['unit_number' => 'A-101']);

        $customer = $this->makeCustomer();
        $contract = $this->makeContract($customer, $unit);

        Sanctum::actingAs($customer);

        $this->getJson('/api/contracts')
            ->assertOk()
            ->assertJsonPath('data.0.unit.unit_number', 'A-101')
            ->assertJsonPath('data.0.unit.property_name', 'Nile View Residences');

        $this->getJson("/api/contracts/{$contract->id}")
            ->assertOk()
            ->assertJsonPath('data.unit.unit_number', 'A-101')
            ->assertJsonPath('data.unit.property_id', $property->id);
    }

    public function test_a_customers_payments_carry_the_contract_unit(): void
    {
        $owner = $this->makeOwner();
        [, $building] = $this->makePortfolio($owner, ['name' => 'Marina Towers']);
        $unit = $this->makeUnit($building, ['unit_number' => 'M-501']);

        $customer = $this->makeCustomer();
        $contract = $this->makeContract($customer, $unit);
        $payment = $this->makePayment($contract, ['reference' => 'PAY-TEST-1']);

        Sanctum::actingAs($customer);

        $this->getJson('/api/payments')
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'PAY-TEST-1')
            ->assertJsonPath('data.0.contract.unit.unit_number', 'M-501');

        $this->getJson("/api/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.contract.unit.property_name', 'Marina Towers');
    }

    public function test_the_added_eager_loads_do_not_widen_what_a_customer_can_see(): void
    {
        $owner = $this->makeOwner();
        [, $building] = $this->makePortfolio($owner);

        $me = $this->makeCustomer();
        $them = $this->makeCustomer();

        $this->makeContract($me, $this->makeUnit($building));
        $theirContract = $this->makeContract($them, $this->makeUnit($building));
        $theirPayment = $this->makePayment($theirContract);

        Sanctum::actingAs($me);

        $this->assertCount(1, $this->getJson('/api/contracts')->assertOk()->json('data'));
        $this->getJson("/api/contracts/{$theirContract->id}")->assertForbidden();
        $this->getJson("/api/payments/{$theirPayment->id}")->assertForbidden();
    }
}
