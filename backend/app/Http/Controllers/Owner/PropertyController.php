<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StorePropertyRequest;
use App\Http\Requests\Owner\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    /** GET /api/owner/properties */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Property::class);

        $properties = Property::query()
            ->ownedBy($request->user())
            ->with(['buildings', 'units'])
            ->withCount('buildings')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('address', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('is_published'), fn ($q) => $q->where('is_published', $request->boolean('is_published')))
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15));

        return PropertyResource::collection($properties)->response();
    }

    /** POST /api/owner/properties */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        Gate::authorize('create', Property::class);

        // owner_id comes from the token, never from the request body.
        $property = Property::create($request->validated() + [
            'owner_id'     => $request->user()->id,
            'status'       => $request->validated('status', 'active'),
            'is_published' => $request->boolean('is_published'),
        ]);

        $property->load(['owner', 'buildings', 'units'])->loadCount('buildings');

        return (new PropertyResource($property))->response()->setStatusCode(201);
    }

    /** GET /api/owner/properties/{property} */
    public function show(Property $property): JsonResponse
    {
        Gate::authorize('view', $property);

        $property->load(['owner', 'buildings.units', 'units'])->loadCount('buildings');

        return (new PropertyResource($property))->response();
    }

    /** PUT /api/owner/properties/{property} */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        Gate::authorize('update', $property);

        $property->update($request->validated());

        $property->load(['owner', 'buildings', 'units'])->loadCount('buildings');

        return (new PropertyResource($property))->response();
    }

    /** DELETE /api/owner/properties/{property} */
    public function destroy(Property $property): JsonResponse
    {
        Gate::authorize('delete', $property);

        // Buildings cascade to units at the database level, but contracts and
        // payments are restricted on delete for good reason — refusing here
        // gives a clear message instead of a database-level failure.
        $blockingContracts = $property->units()
            ->whereHas('contracts')
            ->exists();

        if ($blockingContracts) {
            return response()->json([
                'message' => 'This property still has units under contract. Remove those contracts first.',
            ], 409);
        }

        $hasRequests = $property->units()->whereHas('purchaseRequests')->exists();

        if ($hasRequests) {
            return response()->json([
                'message' => 'This property still has purchase requests against its units. Resolve those first.',
            ], 409);
        }

        $property->delete();

        return response()->json(null, 204);
    }

    /** POST /api/owner/properties/{property}/publish */
    public function publish(Property $property): JsonResponse
    {
        Gate::authorize('publish', $property);

        $property->update(['is_published' => true]);

        return (new PropertyResource($property->fresh()))->response();
    }

    /** POST /api/owner/properties/{property}/unpublish */
    public function unpublish(Property $property): JsonResponse
    {
        Gate::authorize('unpublish', $property);

        $property->update(['is_published' => false]);

        return (new PropertyResource($property->fresh()))->response();
    }
}
