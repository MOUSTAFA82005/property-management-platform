<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreBuildingRequest;
use App\Http\Requests\Owner\UpdateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BuildingController extends Controller
{
    /** GET /api/owner/buildings */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Building::class);

        $buildings = Building::query()
            ->ownedBy($request->user())
            ->with('property')
            ->withCount('units')
            ->when($request->filled('property_id'), fn ($q) => $q->where('property_id', $request->integer('property_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where('name', 'like', $term);
            })
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15));

        return BuildingResource::collection($buildings)->response();
    }

    /** POST /api/owner/buildings */
    public function store(StoreBuildingRequest $request): JsonResponse
    {
        Gate::authorize('create', Building::class);

        $property = Property::findOrFail($request->validated('property_id'));

        // A building may only ever be attached to a property you own.
        Gate::authorize('update', $property);

        $building = Building::create($request->validated() + [
            'floors_count' => $request->validated('floors_count', 1),
        ]);

        $building->load('property')->loadCount('units');

        return (new BuildingResource($building))->response()->setStatusCode(201);
    }

    /** GET /api/owner/buildings/{building} */
    public function show(Building $building): JsonResponse
    {
        Gate::authorize('view', $building);

        $building->load(['property', 'units'])->loadCount('units');

        return (new BuildingResource($building))->response();
    }

    /** PUT /api/owner/buildings/{building} */
    public function update(UpdateBuildingRequest $request, Building $building): JsonResponse
    {
        Gate::authorize('update', $building);

        // Moving a building to another property is only allowed within the
        // owner's own portfolio.
        if ($request->filled('property_id')) {
            Gate::authorize('update', Property::findOrFail($request->validated('property_id')));
        }

        $building->update($request->validated());

        $building->load('property')->loadCount('units');

        return (new BuildingResource($building))->response();
    }

    /** DELETE /api/owner/buildings/{building} */
    public function destroy(Building $building): JsonResponse
    {
        Gate::authorize('delete', $building);

        if ($building->units()->whereHas('contracts')->exists()) {
            return response()->json([
                'message' => 'This building still has units under contract. Remove those contracts first.',
            ], 409);
        }

        if ($building->units()->whereHas('purchaseRequests')->exists()) {
            return response()->json([
                'message' => 'This building still has purchase requests against its units. Resolve those first.',
            ], 409);
        }

        $building->delete();

        return response()->json(null, 204);
    }
}
