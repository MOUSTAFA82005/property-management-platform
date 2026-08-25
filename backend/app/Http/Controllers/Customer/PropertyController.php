<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /** GET /api/properties  — browse published properties */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/properties/{property}  — view property details */
    public function show(Property $property): JsonResponse
    {
        //
    }
}
