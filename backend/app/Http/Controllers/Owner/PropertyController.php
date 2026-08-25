<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /** GET /api/owner/properties */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** POST /api/owner/properties */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/owner/properties/{property} */
    public function show(Property $property): JsonResponse
    {
        //
    }

    /** PUT /api/owner/properties/{property} */
    public function update(Request $request, Property $property): JsonResponse
    {
        //
    }

    /** DELETE /api/owner/properties/{property} */
    public function destroy(Property $property): JsonResponse
    {
        //
    }

    /** POST /api/owner/properties/{property}/publish */
    public function publish(Property $property): JsonResponse
    {
        //
    }

    /** POST /api/owner/properties/{property}/unpublish */
    public function unpublish(Property $property): JsonResponse
    {
        //
    }
}
