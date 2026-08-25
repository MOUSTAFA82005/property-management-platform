<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContractController extends Controller
{
    /** GET /api/contracts  — customer's own contracts */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Contract::class);

        $contracts = $request->user()
            ->contracts()
            ->with(['payments'])
            ->latest()
            ->get();

        return ContractResource::collection($contracts)->response();
    }

    /** GET /api/contracts/{contract}  — view contract details */
    public function show(Request $request, Contract $contract): JsonResponse
    {
        Gate::authorize('view', $contract);

        $contract->load(['user', 'payments']);

        return (new ContractResource($contract))->response();
    }
}
