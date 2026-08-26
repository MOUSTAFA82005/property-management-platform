<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /** GET /api/owner/customers */
    public function index(Request $request): JsonResponse
    {
        $customers = User::query()
            ->where('role', 'customer')
            ->withCount('purchaseRequests')
            ->latest()
            ->get();

        return response()->json([
            'data' => $customers,
        ]);
    }

    /** GET /api/owner/customers/{customer} */
    public function show(User $customer): JsonResponse
    {
        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'Customer not found.',
            ], 404);
        }

        $customer->load([
            'purchaseRequests',
            'contracts.unit.building.property',

        ]);

        return response()->json([
            'data' => $customer,
        ]);
    }
}
