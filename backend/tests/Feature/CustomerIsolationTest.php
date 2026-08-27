<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * A customer must only ever see their own records, enforced server-side —
 * the router guards in the SPA are convenience, not security.
 */
class CustomerIsolationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPortfolios;

    public function test_a_customer_cannot_read_another_customers_contracts_or_payments(): void
    {
        $owner = $this->makeOwner();
        [, $building] = $this->makePortfolio($owner);

        $me = $this->makeCustomer();
        $them = $this->makeCustomer();

        $myContract = $this->makeContract($me, $this->makeUnit($building));
        $theirContract = $this->makeContract($them, $this->makeUnit($building));

        $myPayment = $this->makePayment($myContract);
        $theirPayment = $this->makePayment($theirContract);

        Sanctum::actingAs($me);

        $contractIds = collect($this->getJson('/api/contracts')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($contractIds->contains($myContract->id));
        $this->assertFalse($contractIds->contains($theirContract->id));

        $paymentIds = collect($this->getJson('/api/payments')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($paymentIds->contains($myPayment->id));
        $this->assertFalse($paymentIds->contains($theirPayment->id));

        $this->getJson("/api/contracts/{$theirContract->id}")->assertForbidden();
        $this->getJson("/api/payments/{$theirPayment->id}")->assertForbidden();

        $this->getJson("/api/contracts/{$myContract->id}")->assertOk();
        $this->getJson("/api/payments/{$myPayment->id}")->assertOk();
    }

    public function test_a_customer_cannot_read_another_customers_purchase_requests(): void
    {
        $owner = $this->makeOwner();
        [, $building] = $this->makePortfolio($owner);

        $me = $this->makeCustomer();
        $them = $this->makeCustomer();

        $mine = $this->makePurchaseRequest($me, $this->makeUnit($building));
        $theirs = $this->makePurchaseRequest($them, $this->makeUnit($building));

        Sanctum::actingAs($me);

        $ids = collect($this->getJson('/api/purchase-requests')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($mine->id));
        $this->assertFalse($ids->contains($theirs->id));

        $this->getJson("/api/purchase-requests/{$theirs->id}")->assertForbidden();
    }

    public function test_a_customer_cannot_reach_any_owner_endpoint(): void
    {
        Sanctum::actingAs($this->makeCustomer());

        $this->getJson('/api/owner/properties')->assertForbidden();
        $this->getJson('/api/owner/units')->assertForbidden();
        $this->getJson('/api/owner/buildings')->assertForbidden();
        $this->getJson('/api/owner/customers')->assertForbidden();
        $this->getJson('/api/owner/dashboard')->assertForbidden();
    }

    // -----------------------------------------------------------------
    // Profile
    // -----------------------------------------------------------------

    public function test_the_profile_endpoint_always_returns_the_token_holder(): void
    {
        $me = $this->makeCustomer();
        $them = $this->makeCustomer();

        Sanctum::actingAs($me);

        // There is no id parameter to abuse; a query string changes nothing.
        $this->getJson('/api/profile?id='.$them->id)
            ->assertOk()
            ->assertJsonPath('user.id', $me->id);
    }

    public function test_a_user_can_update_their_own_profile(): void
    {
        $me = $this->makeCustomer(['name' => 'Old Name']);

        Sanctum::actingAs($me);

        $this->putJson('/api/profile', [
            'name'  => 'New Name',
            'phone' => '01099887766',
        ])
            ->assertOk()
            ->assertJsonPath('user.name', 'New Name')
            ->assertJsonPath('user.phone', '01099887766');

        $this->assertDatabaseHas('users', ['id' => $me->id, 'name' => 'New Name']);
    }

    public function test_a_profile_update_cannot_escalate_the_users_role(): void
    {
        $me = $this->makeCustomer();

        Sanctum::actingAs($me);

        $this->putJson('/api/profile', [
            'name'   => 'Sneaky',
            'role'   => 'owner',
            'status' => 'inactive',
        ])->assertOk();

        $me->refresh();

        $this->assertSame('customer', $me->role);
        $this->assertSame('active', $me->status);

        // And the escalation attempt really did not take effect anywhere.
        Sanctum::actingAs($me);
        $this->getJson('/api/owner/dashboard')->assertForbidden();
    }

    public function test_a_profile_update_cannot_take_another_users_email(): void
    {
        $me = $this->makeCustomer();
        $them = $this->makeCustomer(['email' => 'taken@example.com']);

        Sanctum::actingAs($me);

        $this->putJson('/api/profile', ['email' => 'taken@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertSame('taken@example.com', $them->fresh()->email);
    }

    public function test_changing_a_password_requires_the_current_one(): void
    {
        $me = $this->makeCustomer(['password' => 'original-password']);

        Sanctum::actingAs($me);

        $this->putJson('/api/profile', [
            'password'              => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
            'current_password'      => 'wrong-password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('original-password', $me->fresh()->password));

        $this->putJson('/api/profile', [
            'password'              => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
            'current_password'      => 'original-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('brand-new-password', $me->fresh()->password));
    }

    public function test_the_profile_endpoints_require_authentication(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
        $this->putJson('/api/profile', ['name' => 'Nobody'])->assertUnauthorized();
    }
}
