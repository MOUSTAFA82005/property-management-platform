<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties.
     */
    public function index(): AnonymousResourceCollection
    {
        $properties = Property::with(['units', 'manager'])
            ->withCount('units')
            ->latest()
            ->get();

        return PropertyResource::collection($properties);
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property): PropertyResource
    {
        $property->load(['units.building', 'manager'])->loadCount('units');

        return new PropertyResource($property);
    }

    /**
     * Store a newly created property.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'description' => 'nullable|string',
            'property_type' => 'required|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'active';
        }

        if (empty($validated['manager_id'])) {
            $manager = auth()->user() ?? User::first();
            if (!$manager) {
                $manager = User::create([
                    'name' => 'Admin Manager',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('password123'),
                ]);
            }
            $validated['manager_id'] = $manager->id;
        }

        $property = Property::create($validated);

        // Create a default building for this property to anchor units
        $property->buildings()->create([
            'name' => $property->name . ' - Main',
            'floors_count' => 1,
            'description' => 'Main Building',
        ]);

        $property->load(['units', 'manager'])->loadCount('units');

        return (new PropertyResource($property))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified property.
     */
    public function update(Request $request, Property $property): PropertyResource
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'city' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'property_type' => 'sometimes|required|string|max:100',
            'status' => 'sometimes|required|in:active,inactive',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $property->update($validated);

        $property->load(['units', 'manager'])->loadCount('units');

        return new PropertyResource($property);
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Property $property): JsonResponse
    {
        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully.',
        ]);
    }
}