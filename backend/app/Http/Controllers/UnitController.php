<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitResource;
use App\Models\Building;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(): AnonymousResourceCollection
    {
        $units = Unit::with(['building.property'])
            ->latest()
            ->get();

        return UnitResource::collection($units);
    }

    /**
     * Display a listing of units for a specific property.
     */
    public function unitsByProperty(Property $property): AnonymousResourceCollection
    {
        $units = $property->units()
            ->with(['building.property'])
            ->latest()
            ->get();

        return UnitResource::collection($units);
    }

    /**
     * Display the specified unit.
     */
    public function show(Unit $unit): UnitResource
    {
        $unit->load(['building.property']);

        return new UnitResource($unit);
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'building_id' => 'nullable|exists:buildings,id',
            'property_id' => 'nullable|exists:properties,id',
            'unit_number' => 'required|string|max:255',
            'floor' => 'nullable|integer|min:0',
            'unit_type' => 'required|string|max:100',
            'area' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'monthly_rent' => 'required|numeric|min:0',
            'status' => 'nullable|in:available,occupied,reserved',
        ]);

        $buildingId = $validated['building_id'] ?? null;

        if (!$buildingId && !empty($validated['property_id'])) {
            $building = Building::firstOrCreate(
                ['property_id' => $validated['property_id']],
                ['name' => 'Main Building', 'floors_count' => 1]
            );
            $buildingId = $building->id;
        }

        if (!$buildingId) {
            $firstProperty = Property::first();
            if ($firstProperty) {
                $building = Building::firstOrCreate(
                    ['property_id' => $firstProperty->id],
                    ['name' => 'Main Building', 'floors_count' => 1]
                );
                $buildingId = $building->id;
            } else {
                throw ValidationException::withMessages([
                    'property_id' => ['A valid property or building is required to create a unit.'],
                ]);
            }
        }

        $validated['building_id'] = $buildingId;
        unset($validated['property_id']);

        // Validate uniqueness within building
        $exists = Unit::where('building_id', $buildingId)
            ->where('unit_number', $validated['unit_number'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'unit_number' => ['The unit number has already been taken in this building/property.'],
            ]);
        }

        $validated['floor'] = $validated['floor'] ?? 0;
        $validated['bedrooms'] = $validated['bedrooms'] ?? 0;
        $validated['bathrooms'] = $validated['bathrooms'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'available';

        $unit = Unit::create($validated);
        $unit->load(['building.property']);

        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit): UnitResource
    {
        $validated = $request->validate([
            'building_id' => 'nullable|exists:buildings,id',
            'property_id' => 'nullable|exists:properties,id',
            'unit_number' => 'sometimes|required|string|max:255',
            'floor' => 'nullable|integer|min:0',
            'unit_type' => 'sometimes|required|string|max:100',
            'area' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'monthly_rent' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:available,occupied,reserved',
        ]);

        $buildingId = $validated['building_id'] ?? $unit->building_id;

        if (!empty($validated['property_id'])) {
            $building = Building::firstOrCreate(
                ['property_id' => $validated['property_id']],
                ['name' => 'Main Building', 'floors_count' => 1]
            );
            $buildingId = $building->id;
            $validated['building_id'] = $buildingId;
        }

        unset($validated['property_id']);

        if (isset($validated['unit_number']) || isset($validated['building_id'])) {
            $unitNumber = $validated['unit_number'] ?? $unit->unit_number;
            $targetBuildingId = $validated['building_id'] ?? $unit->building_id;

            $exists = Unit::where('building_id', $targetBuildingId)
                ->where('unit_number', $unitNumber)
                ->where('id', '!=', $unit->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'unit_number' => ['The unit number has already been taken in this building/property.'],
                ]);
            }
        }

        $unit->update($validated);
        $unit->load(['building.property']);

        return new UnitResource($unit);
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully.',
        ]);
    }
}