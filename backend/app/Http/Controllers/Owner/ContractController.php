<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContractController extends Controller
{
    /** GET /api/owner/contracts */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Contract::class);

        $contracts = Contract::with(['user', 'unit', 'payments'])
            ->latest()
            ->paginate($request->input('per_page', 15));

        return ContractResource::collection($contracts)->response();
    }

    /** POST /api/owner/contracts */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Contract::class);

        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'unit_id'          => 'required|exists:units,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'monthly_rent'     => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:active,expired,terminated',
            'notes'            => 'nullable|string',
        ]);

        $contract = Contract::create($validated);

        $contract->load(['user', 'unit', 'payments']);

        return (new ContractResource($contract))
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/owner/contracts/{contract} */
    public function show(Contract $contract): JsonResponse
    {
        Gate::authorize('view', $contract);

        $contract->load(['user', 'unit', 'payments']);

        return (new ContractResource($contract))->response();
    }

    /** PUT /api/owner/contracts/{contract} */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        Gate::authorize('update', $contract);

        $validated = $request->validate([
            'start_date'       => 'sometimes|date',
            'end_date'         => 'sometimes|date|after:start_date',
            'monthly_rent'     => 'sometimes|numeric|min:0',
            'security_deposit' => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|in:active,expired,terminated',
            'notes'            => 'nullable|string',
        ]);

        $contract->update($validated);

        $contract->load(['user', 'unit', 'payments']);

        return (new ContractResource($contract))->response();
    }

    /** DELETE /api/owner/contracts/{contract} */
    public function destroy(Contract $contract): JsonResponse
    {
        Gate::authorize('delete', $contract);

        $contract->delete();

        return response()->json(null, 204);
    }
}
