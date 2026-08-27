<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /** GET /api/properties/{property}/units  — available units in a property */
    public function index(Property $property): JsonResponse
    {
        //
    }

    /** GET /api/units/{unit}  — view a single unit */
    public function show(Unit $unit): JsonResponse
    {
        //
    }
}
