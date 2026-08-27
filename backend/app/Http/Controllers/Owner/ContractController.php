<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContractController extends Controller
{
    /** GET /api/owner/contracts */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Contract::class);

        $contracts = Contract::query()
            ->ownedBy($request->user())
            ->with([
                'user',
                'unit.building.property',
                'payments',
            ])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ContractResource::collection($contracts)->response();
    }

    /** POST /api/owner/contracts */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Contract::class);

        $validated = $request->validate([
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'unit_id'          => ['required', 'integer', 'exists:units,id'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after:start_date'],
            'monthly_rent'     => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'status'           => ['nullable', 'in:active,expired,terminated'],
            'notes'            => ['nullable', 'string'],
        ]);

        $customer = User::findOrFail($validated['user_id']);

        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'The selected customer is invalid.',
            ], 422);
        }

        $unit = Unit::with('building.property')
            ->findOrFail($validated['unit_id']);

        if ($unit->building->property->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to use this unit.',
            ], 403);
        }

        if ($unit->status !== 'available') {
            return response()->json([
                'message' => 'This unit is not available.',
            ], 422);
        }

        $contract = Contract::create($validated);

        $unit->update([
            'status' => 'occupied',
        ]);

        $contract->load([
            'user',
            'unit.building.property',
            'payments',
        ]);

        return (new ContractResource($contract))
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/owner/contracts/{contract} */
    public function show(Contract $contract): JsonResponse
    {
        Gate::authorize('view', $contract);

        $contract->load([
            'user',
            'unit.building.property',
            'payments',
        ]);

        return (new ContractResource($contract))->response();
    }

    /** PUT /api/owner/contracts/{contract} */
    public function update(
        Request $request,
        Contract $contract
    ): JsonResponse {
        Gate::authorize('update', $contract);

        $validated = $request->validate([
            'user_id'           => ['sometimes', 'integer', 'exists:users,id'],
            'unit_id'           => ['sometimes', 'integer', 'exists:units,id'],
            'start_date'       => ['sometimes', 'date'],
            'end_date'         => ['sometimes', 'date', 'after:start_date'],
            'monthly_rent'     => ['sometimes', 'numeric', 'min:0'],
            'security_deposit' => ['sometimes', 'numeric', 'min:0'],
            'status'            => ['sometimes', 'in:active,expired,terminated'],
            'notes'             => ['nullable', 'string'],
        ]);

        if (isset($validated['user_id'])) {
            $customer = User::findOrFail($validated['user_id']);

            if ($customer->role !== 'customer') {
                return response()->json([
                    'message' => 'The selected customer is invalid.',
                ], 422);
            }
        }

        if (isset($validated['unit_id'])) {
            $unit = Unit::with('building.property')
                ->findOrFail($validated['unit_id']);

            if ($unit->building->property->owner_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'You are not authorized to use this unit.',
                ], 403);
            }
        }

        $contract->update($validated);

        $contract->load([
            'user',
            'unit.building.property',
            'payments',
        ]);

        return (new ContractResource($contract))->response();
    }

    /** DELETE /api/owner/contracts/{contract} */
    public function destroy(Contract $contract): JsonResponse
    {
        Gate::authorize('delete', $contract);

        $unit = $contract->unit;

        $contract->delete();

        if ($unit && $unit->status === 'occupied') {
            $unit->update([
                'status' => 'available',
            ]);
        }

        return response()->json(null, 204);
    }
}
