<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /** GET /api/contracts — customer's own contracts */
    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::with(['unit'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $contracts,
        ]);
    }

    /** GET /api/contracts/{contract} — view contract details */
    public function show(
        Request $request,
        Contract $contract
    ): JsonResponse {
        abort_unless(
            $contract->customer_id === $request->user()->id,
            403,
            'You are not authorized to access this contract.'
        );

        return response()->json([
            'data' => $contract->load(['unit']),
        ]);
    }
}
