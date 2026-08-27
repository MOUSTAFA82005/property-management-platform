<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Guards the demo dataset.
 *
 * Sprint 4 will build ownership isolation against this data, so the shape it
 * relies on — who is connected to which owner, and who deliberately is not —
 * is asserted here rather than being left to whoever edits the seeder next.
 */
class DemoSeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoDataSeeder::class);
    }

    private function owner(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    // =================================================================
    // Volume
    // =================================================================

    public function test_it_seeds_the_expected_cast_of_users(): void
    {
        $this->assertSame(2, User::where('role', 'owner')->count());
        $this->assertSame(5, User::where('role', 'customer')->count());
        $this->assertSame(7, User::count());
        $this->assertSame(7, User::where('status', 'active')->count());
    }

    public function test_it_seeds_the_expected_portfolio(): void
    {
        $this->assertSame(3, Property::count());
        $this->assertSame(5, Building::count());
        $this->assertSame(12, Unit::count());
        $this->assertSame(5, Contract::count());
        $this->assertSame(20, Payment::count());
        $this->assertSame(7, PurchaseRequest::count());
    }

    public function test_passwords_are_hashed_and_the_documented_one_works(): void
    {
        foreach (User::all() as $user) {
            $this->assertNotSame('password', $user->password);
            $this->assertTrue(
                Hash::check('password', $user->password),
                "The documented development password does not work for {$user->email}."
            );
        }
    }

    // =================================================================
    // Only supported roles and statuses
    // =================================================================

    public function test_no_unsupported_role_is_ever_seeded(): void
    {
        $roles = User::query()->distinct()->pluck('role')->all();

        sort($roles);
        $this->assertSame(['customer', 'owner'], $roles);

        foreach (['admin', 'tenant', 'property_manager', 'manager'] as $forbidden) {
            $this->assertSame(0, User::where('role', $forbidden)->count());
        }
    }

    public function test_units_cover_every_supported_status_and_never_use_sold(): void
    {
        $statuses = Unit::query()->distinct()->pluck('status')->all();
        sort($statuses);

        // The schema has no `sold` status — nothing may introduce one.
        $this->assertSame(['available', 'occupied', 'reserved'], $statuses);
        $this->assertSame(0, Unit::where('status', 'sold')->count());
    }

    public function test_payments_cover_every_supported_status(): void
    {
        $statuses = Payment::query()->distinct()->pluck('status')->all();
        sort($statuses);

        $this->assertSame(['cancelled', 'overdue', 'paid', 'pending'], $statuses);
    }

    public function test_purchase_requests_cover_every_supported_status(): void
    {
        $statuses = PurchaseRequest::query()->distinct()->pluck('status')->all();
        sort($statuses);

        $this->assertSame(['approved', 'cancelled', 'pending', 'rejected'], $statuses);
    }

    public function test_contracts_cover_every_supported_status(): void
    {
        $statuses = Contract::query()->distinct()->pluck('status')->all();
        sort($statuses);

        $this->assertSame(['active', 'expired', 'terminated'], $statuses);
    }

    // =================================================================
    // Relational integrity
    // =================================================================

    public function test_nothing_is_orphaned(): void
    {
        $this->assertSame(0, Property::whereNotIn('owner_id', User::pluck('id'))->count());
        $this->assertSame(0, Building::whereNotIn('property_id', Property::pluck('id'))->count());
        $this->assertSame(0, Unit::whereNotIn('building_id', Building::pluck('id'))->count());
        $this->assertSame(0, Contract::whereNotIn('unit_id', Unit::pluck('id'))->count());
        $this->assertSame(0, Contract::whereNotIn('user_id', User::pluck('id'))->count());
        $this->assertSame(0, Payment::whereNotIn('contract_id', Contract::pluck('id'))->count());
        $this->assertSame(0, PurchaseRequest::whereNotIn('unit_id', Unit::pluck('id'))->count());
        $this->assertSame(0, PurchaseRequest::whereNotIn('customer_id', User::pluck('id'))->count());
    }

    public function test_contracts_and_requests_only_ever_belong_to_customers(): void
    {
        $customerIds = User::where('role', 'customer')->pluck('id');

        $this->assertSame(0, Contract::whereNotIn('user_id', $customerIds)->count());
        $this->assertSame(0, PurchaseRequest::whereNotIn('customer_id', $customerIds)->count());
    }

    public function test_properties_are_split_across_both_owners(): void
    {
        $hassan = $this->owner('owner@propspace.com');
        $nadia = $this->owner('owner2@propspace.com');

        $this->assertSame(2, $hassan->properties()->count());
        $this->assertSame(1, $nadia->properties()->count());
    }

    public function test_both_published_and_unpublished_properties_exist(): void
    {
        $this->assertSame(2, Property::where('is_published', true)->count());
        $this->assertSame(1, Property::where('is_published', false)->count());

        // A public catalog must have stock from both owners to browse.
        $publishedOwners = Property::where('is_published', true)->distinct()->pluck('owner_id');
        $this->assertCount(2, $publishedOwners);
    }

    // =================================================================
    // The story the data tells
    // =================================================================

    public function test_every_occupied_unit_has_exactly_one_active_contract(): void
    {
        foreach (Unit::where('status', 'occupied')->get() as $unit) {
            $this->assertSame(
                1,
                $unit->contracts()->where('status', 'active')->count(),
                "Unit {$unit->unit_number} is occupied but has no single active contract."
            );
        }
    }

    public function test_no_available_unit_is_under_an_active_contract(): void
    {
        foreach (Unit::where('status', 'available')->get() as $unit) {
            $this->assertSame(
                0,
                $unit->contracts()->where('status', 'active')->count(),
                "Unit {$unit->unit_number} is advertised as available but is under an active contract."
            );
        }
    }

    public function test_every_reserved_unit_is_explained_by_an_approved_request(): void
    {
        foreach (Unit::where('status', 'reserved')->get() as $unit) {
            $this->assertGreaterThan(
                0,
                $unit->purchaseRequests()->where('status', 'approved')->count(),
                "Unit {$unit->unit_number} is reserved but nothing explains why."
            );
        }
    }

    public function test_paid_payments_have_a_paid_date_and_unpaid_ones_do_not(): void
    {
        $this->assertSame(0, Payment::where('status', 'paid')->whereNull('paid_date')->count());

        $this->assertSame(
            0,
            Payment::whereIn('status', ['pending', 'overdue', 'cancelled'])->whereNotNull('paid_date')->count()
        );
    }

    public function test_overdue_payments_are_actually_in_the_past(): void
    {
        $this->assertSame(0, Payment::where('status', 'overdue')->whereDate('due_date', '>', now())->count());
        $this->assertGreaterThan(0, Payment::where('status', 'overdue')->count());
    }

    public function test_payment_references_are_unique(): void
    {
        $references = Payment::whereNotNull('reference')->pluck('reference');

        $this->assertCount($references->count(), $references->unique());
    }

    // =================================================================
    // Shape Sprint 4 will test isolation against
    // =================================================================

    /** @return array<int, int> */
    private function customerIdsRelatedTo(User $owner): array
    {
        $unitIds = Unit::whereHas('building.property', fn ($q) => $q->where('owner_id', $owner->id))->pluck('id');

        return Contract::whereIn('unit_id', $unitIds)->pluck('user_id')
            ->merge(PurchaseRequest::whereIn('unit_id', $unitIds)->pluck('customer_id'))
            ->unique()
            ->values()
            ->all();
    }

    public function test_each_owner_has_customers_the_other_owner_must_not_see(): void
    {
        $hassan = $this->owner('owner@propspace.com');
        $nadia = $this->owner('owner2@propspace.com');

        $hassansCustomers = $this->customerIdsRelatedTo($hassan);
        $nadiasCustomers = $this->customerIdsRelatedTo($nadia);

        $youssef = User::where('email', 'customer3@propspace.com')->firstOrFail();
        $salma = User::where('email', 'customer2@propspace.com')->firstOrFail();
        $dina = User::where('email', 'customer4@propspace.com')->firstOrFail();

        // Youssef only ever deals with Nadia.
        $this->assertContains($youssef->id, $nadiasCustomers);
        $this->assertNotContains($youssef->id, $hassansCustomers);

        // Salma and Dina only ever deal with Hassan.
        $this->assertContains($salma->id, $hassansCustomers);
        $this->assertNotContains($salma->id, $nadiasCustomers);
        $this->assertContains($dina->id, $hassansCustomers);
        $this->assertNotContains($dina->id, $nadiasCustomers);

        // And at least one customer is shared, so isolation cannot be faked
        // by simply partitioning the customer table in half.
        $this->assertNotEmpty(array_intersect($hassansCustomers, $nadiasCustomers));
    }

    public function test_both_owners_have_contracts_and_payments_of_their_own(): void
    {
        foreach (['owner@propspace.com', 'owner2@propspace.com'] as $email) {
            $owner = $this->owner($email);

            $unitIds = Unit::whereHas('building.property', fn ($q) => $q->where('owner_id', $owner->id))->pluck('id');
            $contractIds = Contract::whereIn('unit_id', $unitIds)->pluck('id');

            $this->assertGreaterThan(0, $contractIds->count(), "{$email} has no contracts to isolate.");
            $this->assertGreaterThan(
                0,
                Payment::whereIn('contract_id', $contractIds)->count(),
                "{$email} has no payments to isolate."
            );
        }
    }
}
