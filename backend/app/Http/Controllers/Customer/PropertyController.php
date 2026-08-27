<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * GET /api/properties — browse published properties.
     *
     * Public: no authentication. Only published, active properties are ever
     * visible here, and the owner relationship is deliberately not loaded so
     * the catalog does not expose owner contact details.
     */
    public function index(Request $request): JsonResponse
    {
        $properties = $this->publicQuery()
            ->with(['units'])
            ->withCount('buildings')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('address', 'like', $term));
            })
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->input('city')))
            ->when($request->filled('property_type'), fn ($q) => $q->where('property_type', $request->input('property_type')))
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return PropertyResource::collection($properties)->response();
    }

    /** GET /api/properties/{property} — public property details. */
    public function show(Property $property): JsonResponse
    {
        // An unpublished property must be indistinguishable from one that
        // does not exist, so this is a 404 rather than a 403.
        abort_unless($this->isPubliclyVisible($property), 404, 'Property not found.');

        $property->load(['buildings.units'])->loadCount('buildings');

        return (new PropertyResource($property))->response();
    }

    private function publicQuery()
    {
        return Property::query()
            ->where('is_published', true)
            ->where('status', 'active');
    }

    private function isPubliclyVisible(Property $property): bool
    {
        return $property->is_published && $property->status === 'active';
    }
}
