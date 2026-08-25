<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /** GET /api/owner/buildings */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** POST /api/owner/buildings */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/owner/buildings/{building} */
    public function show(Building $building): JsonResponse
    {
        //
    }

    /** PUT /api/owner/buildings/{building} */
    public function update(Request $request, Building $building): JsonResponse
    {
        //
    }

    /** DELETE /api/owner/buildings/{building} */
    public function destroy(Building $building): JsonResponse
    {
        //
    }
}
