<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreUnitRequest;
use App\Http\Requests\Owner\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Building;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    /** GET /api/owner/units */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Unit::class);

        $units = Unit::query()
            ->ownedBy($request->user())
            ->with('building.property')
            ->when($request->filled('building_id'), fn ($q) => $q->where('building_id', $request->integer('building_id')))
            ->when($request->filled('property_id'), fn ($q) => $q->whereHas(
                'building',
                fn ($b) => $b->where('property_id', $request->integer('property_id'))
            ))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q
                    ->where('unit_number', 'like', $term)
                    ->orWhere('unit_type', 'like', $term));
            })
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15));

        return UnitResource::collection($units)->response();
    }

    /** POST /api/owner/units */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        Gate::authorize('create', Unit::class);

        $building = Building::findOrFail($request->validated('building_id'));

        // The unit inherits its ownership from the building, so that is what
        // must be checked before anything is written.
        Gate::authorize('update', $building);

        $this->guardUniqueUnitNumber($building->id, $request->validated('unit_number'));

        $unit = Unit::create($request->validated() + [
            'floor'     => $request->validated('floor', 0),
            'bedrooms'  => $request->validated('bedrooms', 0),
            'bathrooms' => $request->validated('bathrooms', 0),
            'status'    => $request->validated('status', 'available'),
        ]);

        $unit->load('building.property');

        return (new UnitResource($unit))->response()->setStatusCode(201);
    }

    /** GET /api/owner/units/{unit} */
    public function show(Unit $unit): JsonResponse
    {
        Gate::authorize('view', $unit);

        $unit->load(['building.property', 'contracts', 'purchaseRequests']);

        return (new UnitResource($unit))->response();
    }

    /** PUT /api/owner/units/{unit} */
    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        Gate::authorize('update', $unit);

        $targetBuildingId = $unit->building_id;

        if ($request->filled('building_id')) {
            $building = Building::findOrFail($request->validated('building_id'));
            Gate::authorize('update', $building);
            $targetBuildingId = $building->id;
        }

        $this->guardUniqueUnitNumber(
            $targetBuildingId,
            $request->validated('unit_number', $unit->unit_number),
            $unit->id
        );

        $unit->update($request->validated());

        $unit->load('building.property');

        return (new UnitResource($unit))->response();
    }

    /** DELETE /api/owner/units/{unit} */
    public function destroy(Unit $unit): JsonResponse
    {
        Gate::authorize('delete', $unit);

        // contracts.unit_id and purchase_requests.unit_id are both restricted
        // on delete, so refuse with an explanation rather than a 500.
        if ($unit->contracts()->exists()) {
            return response()->json([
                'message' => 'This unit has contracts against it and cannot be deleted.',
            ], 409);
        }

        if ($unit->purchaseRequests()->exists()) {
            return response()->json([
                'message' => 'This unit has purchase requests against it and cannot be deleted.',
            ], 409);
        }

        $unit->delete();

        return response()->json(null, 204);
    }

    /**
     * units has a unique(building_id, unit_number) index; catching it here
     * turns a database error into a normal validation response.
     */
    private function guardUniqueUnitNumber(int $buildingId, string $unitNumber, ?int $ignoreId = null): void
    {
        $exists = Unit::where('building_id', $buildingId)
            ->where('unit_number', $unitNumber)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'unit_number' => ['That unit number is already used in this building.'],
            ]);
        }
    }
}
