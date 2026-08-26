<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /** GET /api/owner/units */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** POST /api/owner/units */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/owner/units/{unit} */
    public function show(Unit $unit): JsonResponse
    {
        //
    }

    /** PUT /api/owner/units/{unit} */
    public function update(Request $request, Unit $unit): JsonResponse
    {
        //
    }

    /** DELETE /api/owner/units/{unit} */
    public function destroy(Unit $unit): JsonResponse
    {
        //
    }
}
