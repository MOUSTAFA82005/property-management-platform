<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyAndUnitTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_properties(): void
    {
        $property = Property::create([
            'name' => 'Sunset Residences',
            'address' => '123 Ocean Drive',
            'city' => 'Miami',
            'description' => 'Luxury beachfront apartments',
            'property_type' => 'Apartment Building',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/properties');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $property->id,
                'name' => 'Sunset Residences',
                'city' => 'Miami',
            ]);
    }

    public function test_can_create_property(): void
    {
        $payload = [
            'name' => 'Palm Tower',
            'address' => '456 Palm St',
            'city' => 'Riyadh',
            'description' => 'Commercial and residential tower',
            'property_type' => 'Commercial Tower',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/properties', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Palm Tower',
                'city' => 'Riyadh',
            ]);

        $this->assertDatabaseHas('properties', [
            'name' => 'Palm Tower',
            'city' => 'Riyadh',
        ]);

        // Asserts building was also created
        $this->assertDatabaseHas('buildings', [
            'name' => 'Palm Tower - Main',
        ]);
    }

    public function test_property_validation_rules(): void
    {
        $response = $this->postJson('/api/properties', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address', 'city', 'property_type']);
    }

    public function test_can_show_property(): void
    {
        $property = Property::create([
            'name' => 'Green Valley Villa',
            'address' => '789 Valley Way',
            'city' => 'Austin',
            'property_type' => 'Residential Complex',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $property->id,
                'name' => 'Green Valley Villa',
            ]);
    }

    public function test_can_update_property(): void
    {
        $property = Property::create([
            'name' => 'Old Name',
            'address' => '100 Main St',
            'city' => 'Dallas',
            'property_type' => 'Residential Complex',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $response = $this->putJson("/api/properties/{$property->id}", [
            'name' => 'Updated Property Name',
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Property Name',
                'status' => 'inactive',
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Updated Property Name',
            'status' => 'inactive',
        ]);
    }

    public function test_can_delete_property(): void
    {
        $property = Property::create([
            'name' => 'Property To Delete',
            'address' => '321 Delete Ave',
            'city' => 'Chicago',
            'property_type' => 'Commercial',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Property deleted successfully.',
            ]);

        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_can_create_unit_under_property(): void
    {
        $property = Property::create([
            'name' => 'Skyline Heights',
            'address' => '500 Skyline Blvd',
            'city' => 'New York',
            'property_type' => 'Apartment Building',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $payload = [
            'property_id' => $property->id,
            'unit_number' => '101A',
            'unit_type' => '2 BHK',
            'floor' => 1,
            'monthly_rent' => 2500.00,
            'area' => 95.5,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'status' => 'available',
        ];

        $response = $this->postJson('/api/units', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'unit_number' => '101A',
                'unit_type' => '2 BHK',
                'monthly_rent' => 2500,
                'status' => 'available',
            ]);

        $this->assertDatabaseHas('units', [
            'unit_number' => '101A',
            'monthly_rent' => 2500.00,
        ]);
    }

    public function test_can_list_units_for_specific_property(): void
    {
        $property = Property::create([
            'name' => 'Marina Bay Residences',
            'address' => '10 Marina Way',
            'city' => 'Dubai',
            'property_type' => 'Apartment Building',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $building = Building::create([
            'property_id' => $property->id,
            'name' => 'Tower A',
            'floors_count' => 10,
        ]);

        Unit::create([
            'building_id' => $building->id,
            'unit_number' => 'A-101',
            'unit_type' => '1 BHK',
            'floor' => 1,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'monthly_rent' => 1800.00,
            'status' => 'available',
        ]);

        Unit::create([
            'building_id' => $building->id,
            'unit_number' => 'A-102',
            'unit_type' => '2 BHK',
            'floor' => 1,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'monthly_rent' => 2400.00,
            'status' => 'occupied',
        ]);

        $response = $this->getJson("/api/properties/{$property->id}/units");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['unit_number' => 'A-101'])
            ->assertJsonFragment(['unit_number' => 'A-102']);
    }

    public function test_can_show_unit(): void
    {
        $property = Property::create([
            'name' => 'Highland Park',
            'address' => '99 Park Ave',
            'city' => 'Seattle',
            'property_type' => 'Residential',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $building = Building::create([
            'property_id' => $property->id,
            'name' => 'Main Building',
            'floors_count' => 5,
        ]);

        $unit = Unit::create([
            'building_id' => $building->id,
            'unit_number' => '301',
            'unit_type' => 'Studio',
            'floor' => 3,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'monthly_rent' => 1200.00,
            'status' => 'available',
        ]);

        $response = $this->getJson("/api/units/{$unit->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $unit->id,
                'unit_number' => '301',
                'unit_type' => 'Studio',
            ]);
    }

    public function test_can_update_unit(): void
    {
        $property = Property::create([
            'name' => 'Cedar Crest',
            'address' => '88 Cedar Rd',
            'city' => 'Portland',
            'property_type' => 'Residential',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $building = Building::create([
            'property_id' => $property->id,
            'name' => 'Main',
            'floors_count' => 3,
        ]);

        $unit = Unit::create([
            'building_id' => $building->id,
            'unit_number' => '102',
            'unit_type' => '1 BHK',
            'floor' => 1,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'monthly_rent' => 1100.00,
            'status' => 'available',
        ]);

        $response = $this->putJson("/api/units/{$unit->id}", [
            'monthly_rent' => 1350.00,
            'status' => 'occupied',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'monthly_rent' => 1350,
                'status' => 'occupied',
            ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'monthly_rent' => 1350.00,
            'status' => 'occupied',
        ]);
    }

    public function test_can_delete_unit(): void
    {
        $property = Property::create([
            'name' => 'Apex Towers',
            'address' => '1000 Apex Way',
            'city' => 'Denver',
            'property_type' => 'Commercial',
            'status' => 'active',
            'manager_id' => $this->user->id,
        ]);

        $building = Building::create([
            'property_id' => $property->id,
            'name' => 'Main',
            'floors_count' => 2,
        ]);

        $unit = Unit::create([
            'building_id' => $building->id,
            'unit_number' => '404',
            'unit_type' => 'Office',
            'floor' => 4,
            'bedrooms' => 0,
            'bathrooms' => 1,
            'monthly_rent' => 3000.00,
            'status' => 'available',
        ]);

        $response = $this->deleteJson("/api/units/{$unit->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Unit deleted successfully.',
            ]);

        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }
}

