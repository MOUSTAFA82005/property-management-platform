<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * GET /api/properties/{property}/units — units in a published property.
     *
     * Public. Defaults to units a visitor could actually act on, but accepts
     * ?status= so a property page can show its full inventory.
     */
    public function index(Request $request, Property $property): JsonResponse
    {
        abort_unless($property->is_published && $property->status === 'active', 404, 'Property not found.');

        $units = Unit::query()
            ->whereHas('building', fn ($b) => $b->where('property_id', $property->id))
            ->with('building.property')
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', $request->input('status')),
                fn ($q) => $q->where('status', 'available')
            )
            ->orderBy('unit_number')
            ->get();

        return UnitResource::collection($units)->response();
    }

    /** GET /api/units/{unit} — public unit details. */
    public function show(Unit $unit): JsonResponse
    {
        // Units inside an unpublished property are not public.
        abort_unless(
            Unit::query()->publiclyVisible()->whereKey($unit->id)->exists(),
            404,
            'Unit not found.'
        );

        $unit->load('building.property');

        return (new UnitResource($unit))->response();
    }
}
