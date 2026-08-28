<?php

namespace Tests\Support;

use App\Models\Building;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Models\User;

/**
 * Controlled fixtures for the authorization tests.
 *
 * These deliberately do not use the demo seeder: an isolation test that
 * depends on seed ordering stops proving anything the moment the seeder is
 * edited. Each test builds exactly the two portfolios it needs.
 */
trait BuildsPortfolios
{
    private static int $unitSequence = 0;

    protected function makeOwner(array $attributes = []): User
    {
        return User::factory()->owner()->create($attributes);
    }

    protected function makeCustomer(array $attributes = []): User
    {
        return User::factory()->customer()->create($attributes);
    }

    protected function makeProperty(User $owner, array $attributes = []): Property
    {
        return Property::query()->create(array_merge([
            'owner_id'      => $owner->id,
            'name'          => 'Property '.fake()->unique()->numberBetween(1000, 9999),
            'address'       => '1 Test Street',
            'city'          => 'Cairo',
            'property_type' => 'Apartment Building',
            'status'        => 'active',
            'is_published'  => true,
        ], $attributes));
    }

    protected function makeBuilding(Property $property, array $attributes = []): Building
    {
        return Building::query()->create(array_merge([
            'property_id'  => $property->id,
            'name'         => 'Building '.fake()->unique()->numberBetween(1000, 9999),
            'floors_count' => 4,
        ], $attributes));
    }

    protected function makeUnit(Building $building, array $attributes = []): Unit
    {
        return Unit::query()->create(array_merge([
            'building_id'  => $building->id,
            'unit_number'  => 'U-'.(++self::$unitSequence),
            'floor'        => 1,
            'unit_type'    => 'Apartment',
            'area'         => 100,
            'bedrooms'     => 2,
            'bathrooms'    => 1,
            'monthly_rent' => 12000,
            'status'       => 'available',
        ], $attributes));
    }

    /**
     * A whole portfolio in one call.
     *
     * @return array{0: Property, 1: Building, 2: Unit}
     */
    protected function makePortfolio(User $owner, array $property = [], array $unit = []): array
    {
        $prop = $this->makeProperty($owner, $property);
        $building = $this->makeBuilding($prop);

        return [$prop, $building, $this->makeUnit($building, $unit)];
    }

    protected function makeContract(User $customer, Unit $unit, array $attributes = []): Contract
    {
        return Contract::query()->create(array_merge([
            'user_id'          => $customer->id,
            'unit_id'          => $unit->id,
            'start_date'       => now()->subMonth()->toDateString(),
            'end_date'         => now()->addYear()->toDateString(),
            'monthly_rent'     => 12000,
            'security_deposit' => 24000,
            'status'           => 'active',
        ], $attributes));
    }

    protected function makePayment(Contract $contract, array $attributes = []): Payment
    {
        return Payment::query()->create(array_merge([
            'contract_id' => $contract->id,
            'amount'      => 12000,
            'due_date'    => now()->toDateString(),
            'status'      => 'pending',
            'reference'   => 'REF-'.fake()->unique()->numberBetween(100000, 999999),
        ], $attributes));
    }

    protected function makePurchaseRequest(User $customer, Unit $unit, array $attributes = []): PurchaseRequest
    {
        return PurchaseRequest::query()->create(array_merge([
            'customer_id' => $customer->id,
            'unit_id'     => $unit->id,
            'status'      => 'pending',
        ], $attributes));
    }
}
