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
 * PropSpace has exactly one owner and six customers. That composition, and
 * the story the rows tell about each other, is asserted here rather than
 * being left to whoever edits the seeder next.
 *
 * Ownership isolation itself is proved in OwnerIsolationTest, which builds
 * its own portfolios instead of leaning on this data.
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

    public function test_it_seeds_exactly_one_owner_and_six_customers(): void
    {
        $this->assertSame(1, User::where('role', 'owner')->count());
        $this->assertSame(6, User::where('role', 'customer')->count());
        $this->assertSame(7, User::count());
        $this->assertSame(7, User::where('status', 'active')->count());

        $this->assertSame('owner@propspace.com', User::where('role', 'owner')->value('email'));
    }

    public function test_it_seeds_the_expected_portfolio(): void
    {
        $this->assertSame(3, Property::count());
        $this->assertSame(5, Building::count());
        $this->assertSame(12, Unit::count());
        $this->assertSame(5, Contract::count());
        $this->assertSame(20, Payment::count());
        $this->assertSame(8, PurchaseRequest::count());
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

    public function test_every_property_belongs_to_the_single_owner(): void
    {
        $hassan = $this->owner('owner@propspace.com');

        $this->assertSame(3, $hassan->properties()->count());
        $this->assertSame(0, Property::where('owner_id', '!=', $hassan->id)->count());
    }

    public function test_both_published_and_unpublished_properties_exist(): void
    {
        $this->assertSame(2, Property::where('is_published', true)->count());
        $this->assertSame(1, Property::where('is_published', false)->count());
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
    // The owner's relationships
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

    public function test_every_seeded_customer_reaches_the_owner(): void
    {
        $related = $this->customerIdsRelatedTo($this->owner('owner@propspace.com'));

        // The owner customer list is built from contracts and requests, so a
        // customer nothing connects would silently vanish from the demo.
        foreach (User::where('role', 'customer')->get() as $customer) {
            $this->assertContains(
                $customer->id,
                $related,
                "{$customer->email} is seeded but connected to nothing the owner can see."
            );
        }
    }

    public function test_one_customer_enquires_without_holding_a_contract(): void
    {
        $nour = User::where('email', 'customer6@propspace.com')->firstOrFail();

        $this->assertSame(0, Contract::where('user_id', $nour->id)->count());
        $this->assertGreaterThan(0, PurchaseRequest::where('customer_id', $nour->id)->count());
    }

    public function test_the_owner_has_contracts_and_payments_to_manage(): void
    {
        $owner = $this->owner('owner@propspace.com');

        $unitIds = Unit::whereHas('building.property', fn ($q) => $q->where('owner_id', $owner->id))->pluck('id');
        $contractIds = Contract::whereIn('unit_id', $unitIds)->pluck('id');

        $this->assertSame(Contract::count(), $contractIds->count());
        $this->assertSame(Payment::count(), Payment::whereIn('contract_id', $contractIds)->count());
    }
}
