<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * A single coherent demo scenario, not a pile of random rows.
 *
 * ----------------------------------------------------------------------
 * DEVELOPMENT CREDENTIALS — every seeded account uses the password:
 *
 *     password
 *
 *   OWNERS
 *     owner@propspace.com      Hassan Farouk   — Nile View, Palm Gardens
 *     owner2@propspace.com     Nadia Mansour   — Alexandria Marina
 *
 *   CUSTOMERS
 *     customer@propspace.com   Omar Sabry      — tenant of Hassan, enquiring with Nadia
 *     customer2@propspace.com  Salma Adel      — tenant of Hassan
 *     customer3@propspace.com  Youssef Ibrahim — tenant of Nadia only
 *     customer4@propspace.com  Dina Hafez      — former tenant of Hassan
 *     customer5@propspace.com  Karim Nassar    — former tenant of Nadia, enquiring with Hassan
 * ----------------------------------------------------------------------
 *
 * The relationships are deliberately asymmetric so ownership isolation is
 * actually testable: Youssef is connected to Nadia and never to Hassan,
 * while Salma and Dina are connected to Hassan and never to Nadia. An owner
 * endpoint that leaks will show it immediately.
 */
class DemoDataSeeder extends Seeder
{
    private int $paymentReference = 0;

    public function run(): void
    {
        $owners = $this->seedOwners();
        $customers = $this->seedCustomers();
        $units = $this->seedPortfolio($owners);
        $contracts = $this->seedContracts($customers, $units);

        $this->seedPayments($contracts);
        $this->seedPurchaseRequests($customers, $units);

        $this->report();
    }

    // =================================================================
    // Users
    // =================================================================

    /** @return array<string, User> */
    private function seedOwners(): array
    {
        return [
            'hassan' => $this->user('owner@propspace.com', 'Hassan Farouk', 'owner', '01012000001'),
            'nadia'  => $this->user('owner2@propspace.com', 'Nadia Mansour', 'owner', '01012000002'),
        ];
    }

    /** @return array<string, User> */
    private function seedCustomers(): array
    {
        return [
            'omar'    => $this->user('customer@propspace.com', 'Omar Sabry', 'customer', '01098000001'),
            'salma'   => $this->user('customer2@propspace.com', 'Salma Adel', 'customer', '01098000002'),
            'youssef' => $this->user('customer3@propspace.com', 'Youssef Ibrahim', 'customer', '01098000003'),
            'dina'    => $this->user('customer4@propspace.com', 'Dina Hafez', 'customer', '01098000004'),
            'karim'   => $this->user('customer5@propspace.com', 'Karim Nassar', 'customer', '01098000005'),
        ];
    }

    private function user(string $email, string $name, string $role, string $phone): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name'   => $name,
                'phone'  => $phone,
                'role'   => $role,
                'status' => 'active',
                // The `hashed` cast on the User model hashes this on write.
                'password' => 'password',
            ]
        );
    }

    // =================================================================
    // Properties → buildings → units
    // =================================================================

    /**
     * @param  array<string, User>  $owners
     * @return array<string, Unit>
     */
    private function seedPortfolio(array $owners): array
    {
        // ---- Hassan, property 1: published, the main demo property -----
        $nileView = $this->property($owners['hassan'], [
            'name'          => 'Nile View Residences',
            'address'       => '18 Corniche El Nil, Garden City',
            'city'          => 'Cairo',
            'description'   => 'Riverside apartments with balconies overlooking the Nile, walking distance from Garden City.',
            'property_type' => 'Apartment Building',
            'is_published'  => true,
        ]);

        $nileA = $this->building($nileView, 'Tower A', 6, 'North tower, river-facing units.');
        $nileB = $this->building($nileView, 'Tower B', 4, 'South tower, courtyard-facing units.');

        // ---- Hassan, property 2: unpublished, so public browsing can be
        //      tested against a property that must stay hidden -----------
        $palmGardens = $this->property($owners['hassan'], [
            'name'          => 'Palm Gardens Compound',
            'address'       => 'Plot 44, Zayed Dunes, 6th of October',
            'city'          => '6th of October',
            'description'   => 'Gated compound of townhouses and villas around a central garden. Not yet listed publicly.',
            'property_type' => 'Residential Compound',
            'is_published'  => false,
        ]);

        $palmOne = $this->building($palmGardens, 'Block 1', 2, 'Townhouse block facing the main garden.');
        $palmTwo = $this->building($palmGardens, 'Block 2', 2, 'Standalone villas along the eastern wall.');

        // ---- Nadia, property 3: published ------------------------------
        $marina = $this->property($owners['nadia'], [
            'name'          => 'Alexandria Marina Towers',
            'address'       => '7 El Geish Road, Stanley',
            'city'          => 'Alexandria',
            'description'   => 'Sea-view tower next to Stanley Bridge with direct access to the marina promenade.',
            'property_type' => 'Apartment Building',
            'is_published'  => true,
        ]);

        $marinaTower = $this->building($marina, 'Marina Tower', 12, 'Single tower, twelve floors, sea-facing from floor five up.');

        return [
            // Hassan · Nile View · Tower A
            'A-101' => $this->unit($nileA, 'A-101', 1, 'Apartment', 110.00, 2, 1, 14000, 'occupied'),
            'A-102' => $this->unit($nileA, 'A-102', 1, 'Apartment', 75.00, 1, 1, 9500, 'available'),
            'A-201' => $this->unit($nileA, 'A-201', 2, 'Apartment', 145.00, 3, 2, 19000, 'reserved'),

            // Hassan · Nile View · Tower B
            'B-101' => $this->unit($nileB, 'B-101', 1, 'Studio', 48.00, 0, 1, 7000, 'available'),
            'B-102' => $this->unit($nileB, 'B-102', 1, 'Apartment', 105.00, 2, 2, 13500, 'occupied'),

            // Hassan · Palm Gardens (unpublished)
            'G-01' => $this->unit($palmOne, 'G-01', 0, 'Townhouse', 210.00, 4, 3, 27000, 'available'),
            'G-02' => $this->unit($palmOne, 'G-02', 0, 'Townhouse', 210.00, 4, 3, 27000, 'reserved'),
            'G-11' => $this->unit($palmTwo, 'G-11', 0, 'Villa', 320.00, 5, 4, 42000, 'available'),

            // Nadia · Alexandria Marina
            'M-501' => $this->unit($marinaTower, 'M-501', 5, 'Apartment', 120.00, 2, 2, 16000, 'occupied'),
            'M-502' => $this->unit($marinaTower, 'M-502', 5, 'Apartment', 68.00, 1, 1, 10000, 'available'),
            'M-901' => $this->unit($marinaTower, 'M-901', 9, 'Penthouse', 260.00, 4, 3, 38000, 'reserved'),
            'M-902' => $this->unit($marinaTower, 'M-902', 9, 'Apartment', 160.00, 3, 3, 22000, 'available'),
        ];
    }

    /** @param array<string, mixed> $attributes */
    private function property(User $owner, array $attributes): Property
    {
        return Property::updateOrCreate(
            ['name' => $attributes['name']],
            array_merge($attributes, [
                'owner_id' => $owner->id,
                'status'   => 'active',
            ])
        );
    }

    private function building(Property $property, string $name, int $floors, string $description): Building
    {
        return Building::updateOrCreate(
            ['property_id' => $property->id, 'name' => $name],
            ['floors_count' => $floors, 'description' => $description]
        );
    }

    private function unit(
        Building $building,
        string $number,
        int $floor,
        string $type,
        float $area,
        int $bedrooms,
        int $bathrooms,
        float $rent,
        string $status,
    ): Unit {
        return Unit::updateOrCreate(
            ['building_id' => $building->id, 'unit_number' => $number],
            [
                'floor'        => $floor,
                'unit_type'    => $type,
                'area'         => $area,
                'bedrooms'     => $bedrooms,
                'bathrooms'    => $bathrooms,
                'monthly_rent' => $rent,
                'status'       => $status,
            ]
        );
    }

    // =================================================================
    // Contracts
    // =================================================================

    /**
     * Every `occupied` unit has exactly one active contract, and the two
     * ended contracts explain why their units are free again.
     *
     * @param  array<string, User>  $customers
     * @param  array<string, Unit>  $units
     * @return array<string, Contract>
     */
    private function seedContracts(array $customers, array $units): array
    {
        return [
            // --- Hassan's contracts ------------------------------------
            'omar_a101' => $this->contract(
                $customers['omar'], $units['A-101'],
                now()->subMonths(5)->startOfMonth(), now()->addMonths(7)->endOfMonth(),
                14000, 28000, 'active',
                'Twelve-month lease, rent due on the 1st of each month.'
            ),
            'salma_b102' => $this->contract(
                $customers['salma'], $units['B-102'],
                now()->subMonths(2)->startOfMonth(), now()->addMonths(10)->endOfMonth(),
                13500, 27000, 'active',
                'Renewal of a previous lease in the same tower.'
            ),
            // Dina lived in B-102 before Salma — this is why an occupied unit
            // has two contracts against it.
            'dina_b102' => $this->contract(
                $customers['dina'], $units['B-102'],
                now()->subMonths(26)->startOfMonth(), now()->subMonths(14)->endOfMonth(),
                12000, 24000, 'expired',
                'Completed lease. Deposit returned in full.'
            ),

            // --- Nadia's contracts -------------------------------------
            'youssef_m501' => $this->contract(
                $customers['youssef'], $units['M-501'],
                now()->subMonths(8)->startOfMonth(), now()->addMonths(4)->endOfMonth(),
                16000, 32000, 'active',
                'Sea-view unit, includes parking space P-14.'
            ),
            // Karim broke his lease early, which is why M-502 is available.
            'karim_m502' => $this->contract(
                $customers['karim'], $units['M-502'],
                now()->subMonths(10)->startOfMonth(), now()->subMonths(3)->endOfMonth(),
                9800, 19600, 'terminated',
                'Terminated early at the tenant\'s request; one month notice served.'
            ),
        ];
    }

    private function contract(
        User $customer,
        Unit $unit,
        Carbon $start,
        Carbon $end,
        float $rent,
        float $deposit,
        string $status,
        string $notes,
    ): Contract {
        return Contract::updateOrCreate(
            ['user_id' => $customer->id, 'unit_id' => $unit->id, 'start_date' => $start->toDateString()],
            [
                'end_date'         => $end->toDateString(),
                'monthly_rent'     => $rent,
                'security_deposit' => $deposit,
                'status'           => $status,
                'notes'            => $notes,
            ]
        );
    }

    // =================================================================
    // Payments
    // =================================================================

    /** @param array<string, Contract> $contracts */
    private function seedPayments(array $contracts): void
    {
        // Rent falls due on the 1st of each month, so every schedule below is
        // anchored to a month boundary and runs without gaps.

        // Omar — paid through June, then two missed months and one upcoming.
        $this->payment($contracts['omar_a101'], 14000, $this->monthStart(-4), $this->monthStart(-4)->addDays(2), 'paid', 'bank_transfer');
        $this->payment($contracts['omar_a101'], 14000, $this->monthStart(-3), $this->monthStart(-3)->addDay(), 'paid', 'bank_transfer');
        $this->payment($contracts['omar_a101'], 14000, $this->monthStart(-2), $this->monthStart(-2)->addDays(3), 'paid', 'instapay');
        $this->payment($contracts['omar_a101'], 14000, $this->monthStart(-1), null, 'overdue', null, 'Reminder sent. No response yet.');
        $this->payment($contracts['omar_a101'], 14000, $this->monthStart(0), null, 'overdue', null, 'Second missed month. Escalated to the owner.');
        $this->payment($contracts['omar_a101'], 14000, $this->monthStart(1), null, 'pending');

        // Salma — up to date, plus a cancelled duplicate invoice.
        $this->payment($contracts['salma_b102'], 13500, $this->monthStart(-2), $this->monthStart(-2), 'paid', 'credit_card');
        $this->payment($contracts['salma_b102'], 13500, $this->monthStart(-1), $this->monthStart(-1)->addDays(2), 'paid', 'credit_card');
        $this->payment($contracts['salma_b102'], 13500, $this->monthStart(0), $this->monthStart(0)->addDay(), 'paid', 'instapay');
        $this->payment($contracts['salma_b102'], 13500, $this->monthStart(1), null, 'pending');
        $this->payment($contracts['salma_b102'], 13500, $this->monthStart(-1), null, 'cancelled', null, 'Duplicate invoice raised in error, cancelled before collection.');

        // Dina — historical, fully settled inside her lease window.
        $this->payment($contracts['dina_b102'], 12000, $this->monthStart(-16), $this->monthStart(-16), 'paid', 'cash');
        $this->payment($contracts['dina_b102'], 12000, $this->monthStart(-15), $this->monthStart(-15), 'paid', 'cash');

        // Youssef — Nadia's active tenant, one month behind.
        $this->payment($contracts['youssef_m501'], 16000, $this->monthStart(-3), $this->monthStart(-3), 'paid', 'bank_transfer');
        $this->payment($contracts['youssef_m501'], 16000, $this->monthStart(-2), $this->monthStart(-2)->addDay(), 'paid', 'bank_transfer');
        $this->payment($contracts['youssef_m501'], 16000, $this->monthStart(-1), $this->monthStart(-1)->addDays(4), 'paid', 'instapay');
        $this->payment($contracts['youssef_m501'], 16000, $this->monthStart(0), null, 'overdue', null, 'Tenant travelling; payment promised on return.');
        $this->payment($contracts['youssef_m501'], 16000, $this->monthStart(1), null, 'pending');

        // Karim — one settled month, then cancelled when the lease ended early.
        $this->payment($contracts['karim_m502'], 9800, $this->monthStart(-5), $this->monthStart(-5), 'paid', 'bank_transfer');
        $this->payment($contracts['karim_m502'], 9800, $this->monthStart(-2), null, 'cancelled', null, 'Void: contract terminated before this period began.');
    }

    /** The 1st of the month `$offset` months from now. */
    private function monthStart(int $offset): Carbon
    {
        return now()->startOfMonth()->addMonths($offset);
    }

    private function payment(
        Contract $contract,
        float $amount,
        Carbon $dueDate,
        ?Carbon $paidDate,
        string $status,
        ?string $method = null,
        ?string $notes = null,
    ): Payment {
        $reference = sprintf('PAY-%s-%04d', now()->year, ++$this->paymentReference);

        return Payment::updateOrCreate(
            ['reference' => $reference],
            [
                'contract_id'    => $contract->id,
                'amount'         => $amount,
                'due_date'       => $dueDate->toDateString(),
                'paid_date'      => $paidDate?->toDateString(),
                'payment_method' => $method,
                'status'         => $status,
                'notes'          => $notes,
            ]
        );
    }

    // =================================================================
    // Purchase requests
    // =================================================================

    /**
     * Each `reserved` unit is reserved because a request against it was
     * approved — the unit statuses and the request statuses agree.
     *
     * @param  array<string, User>  $customers
     * @param  array<string, Unit>  $units
     */
    private function seedPurchaseRequests(array $customers, array $units): void
    {
        // Approved → the three reserved units.
        $this->purchaseRequest($customers['salma'], $units['A-201'], 'approved',
            'Approved. Viewing completed, paperwork with the customer.');
        $this->purchaseRequest($customers['karim'], $units['G-02'], 'approved',
            'Approved subject to the compound handover date.');
        $this->purchaseRequest($customers['omar'], $units['M-901'], 'approved',
            'Approved. Customer requested a payment plan over 24 months.');

        // Pending → waiting on the owner, for the owner-side workflow.
        $this->purchaseRequest($customers['dina'], $units['A-102'], 'pending',
            'Interested in the one-bedroom. Asked whether parking is included.');
        $this->purchaseRequest($customers['salma'], $units['G-11'], 'pending',
            'Would like a viewing this weekend if possible.');

        // Rejected and cancelled → the closed states.
        $this->purchaseRequest($customers['karim'], $units['M-902'], 'rejected',
            'Rejected: unit is being held for an existing tenant transferring floors.');
        $this->purchaseRequest($customers['omar'], $units['B-101'], 'cancelled',
            'Customer withdrew the request after finding a larger unit.');
    }

    private function purchaseRequest(User $customer, Unit $unit, string $status, string $notes): PurchaseRequest
    {
        return PurchaseRequest::updateOrCreate(
            ['customer_id' => $customer->id, 'unit_id' => $unit->id],
            ['status' => $status, 'notes' => $notes]
        );
    }

    // =================================================================

    private function report(): void
    {
        $this->command?->info(sprintf(
            'Demo data ready: %d users, %d properties, %d buildings, %d units, %d contracts, %d payments, %d purchase requests.',
            User::count(),
            Property::count(),
            Building::count(),
            Unit::count(),
            Contract::count(),
            Payment::count(),
            PurchaseRequest::count(),
        ));

        $this->command?->line('  Owners:    owner@propspace.com / owner2@propspace.com');
        $this->command?->line('  Customers: customer@propspace.com … customer5@propspace.com');
        $this->command?->line('  Password:  password');
    }
}
