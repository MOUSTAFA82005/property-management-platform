<?php

namespace Tests\Feature;

use App\Notifications\ContractNotification;
use App\Notifications\PaymentNotification;
use App\Notifications\PurchaseRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\BuildsPortfolios;
use Tests\TestCase;

/**
 * Notifications are raised by real transitions and are private to their owner.
 *
 * Two things matter here: that the events which fire them are ones the app
 * actually performs, and that no request can reach another user's row.
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase, BuildsPortfolios;

    // =================================================================
    // Owner actions notify the customer
    // =================================================================

    public function test_creating_a_contract_notifies_the_customer_not_the_owner(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);

        Sanctum::actingAs($owner);

        $this->postJson('/api/owner/contracts', [
            'user_id' => $customer->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'monthly_rent' => 12000,
        ])->assertCreated();

        $this->assertSame(1, $customer->notifications()->count());
        $this->assertSame(0, $owner->notifications()->count());

        $payload = $customer->notifications()->first()->data;
        $this->assertSame(ContractNotification::CREATED, $payload['type']);
        $this->assertStringContainsString('/contracts/', $payload['url']);
    }

    public function test_editing_and_deleting_a_contract_each_notify_the_customer(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);
        $contract = $this->makeContract($customer, $unit);

        Sanctum::actingAs($owner);

        $this->putJson("/api/owner/contracts/{$contract->id}", ['status' => 'terminated'])->assertOk();
        $this->deleteJson("/api/owner/contracts/{$contract->id}")->assertNoContent();

        $types = $customer->notifications()->pluck('data')->map(fn ($d) => $d['type'])->all();

        $this->assertContains(ContractNotification::UPDATED, $types);
        $this->assertContains(ContractNotification::DELETED, $types);
    }

    public function test_a_payment_notifies_only_on_a_real_status_change(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);
        $contract = $this->makeContract($customer, $unit);
        $payment = $this->makePayment($contract);

        Sanctum::actingAs($owner);

        // Editing a note is not worth interrupting anyone for.
        $this->putJson("/api/owner/payments/{$payment->id}", ['notes' => 'Chased by email'])->assertOk();
        $this->assertSame(0, $customer->notifications()->count());

        $this->putJson("/api/owner/payments/{$payment->id}", ['status' => 'paid'])->assertOk();
        $this->assertSame(1, $customer->notifications()->count());
        $this->assertSame(
            PaymentNotification::UPDATED,
            $customer->notifications()->first()->data['type'],
        );
    }

    // =================================================================
    // Customer actions notify the owner
    // =================================================================

    public function test_submitting_a_request_notifies_the_owner_of_the_unit(): void
    {
        $owner = $this->makeOwner();
        $otherOwner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);

        Sanctum::actingAs($customer);

        $this->postJson('/api/purchase-requests', ['unit_id' => $unit->id])->assertCreated();

        $this->assertSame(1, $owner->notifications()->count());
        $this->assertSame(0, $otherOwner->notifications()->count());
        $this->assertSame(0, $customer->notifications()->count());

        $payload = $owner->notifications()->first()->data;
        $this->assertSame(PurchaseRequestNotification::SUBMITTED, $payload['type']);
        $this->assertStringContainsString('/owner/purchase-requests/', $payload['url']);
    }

    public function test_approving_a_request_notifies_the_customer_who_raised_it(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);
        $request = $this->makePurchaseRequest($customer, $unit);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/purchase-requests/{$request->id}/approve")->assertOk();

        $payload = $customer->notifications()->first()->data;
        $this->assertSame(PurchaseRequestNotification::APPROVED, $payload['type']);
        // The customer's link is their own route, not the owner portal's.
        $this->assertSame("/purchase-requests/{$request->id}", $payload['url']);
    }

    // =================================================================
    // The API
    // =================================================================

    public function test_a_user_only_ever_sees_their_own_notifications(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        $other = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);

        $customer->notify(ContractNotification::created($this->makeContract($customer, $unit)));

        Sanctum::actingAs($other);

        $this->getJson('/api/notifications')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('count', 0);

        // And another user's id is a 404, not a silent success.
        $foreignId = $customer->notifications()->first()->id;
        $this->postJson("/api/notifications/{$foreignId}/read")->assertNotFound();

        $this->assertNull($customer->notifications()->first()->read_at);
    }

    public function test_marking_one_and_all_as_read_updates_the_unread_count(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);
        $contract = $this->makeContract($customer, $unit);

        $customer->notify(ContractNotification::created($contract));
        $customer->notify(ContractNotification::updated($contract));

        Sanctum::actingAs($customer);

        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('count', 2);

        $first = $customer->notifications()->first()->id;
        $this->postJson("/api/notifications/{$first}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('count', 1);

        $this->postJson('/api/notifications/read-all')->assertOk()->assertJsonPath('count', 0);
        $this->getJson('/api/notifications?unread=1')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_notification_endpoints_require_authentication(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->getJson('/api/notifications/unread-count')->assertUnauthorized();
        $this->postJson('/api/notifications/read-all')->assertUnauthorized();
    }

    public function test_the_list_is_newest_first_and_carries_the_render_fields(): void
    {
        $owner = $this->makeOwner();
        $customer = $this->makeCustomer();
        [, , $unit] = $this->makePortfolio($owner);
        $contract = $this->makeContract($customer, $unit);

        // Sent a moment apart: `created_at` is second-resolution, so two
        // notifications written in the same second have no defined order.
        $customer->notify(ContractNotification::created($contract));
        $this->travel(2)->seconds();
        $customer->notify(ContractNotification::updated($contract));
        $this->travelBack();

        Sanctum::actingAs($customer);

        $data = $this->getJson('/api/notifications')->assertOk()->json('data');

        $this->assertSame(ContractNotification::UPDATED, $data[0]['type']);
        $this->assertSame(ContractNotification::CREATED, $data[1]['type']);

        foreach (['id', 'type', 'title', 'message', 'url', 'is_read', 'created_at'] as $field) {
            $this->assertArrayHasKey($field, $data[0]);
        }
    }
}
