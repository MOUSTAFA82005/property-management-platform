<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /** GET /api/owner/contracts */
    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::with(['customer', 'unit'])
            ->whereHas('unit.building.property', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $contracts,
        ]);
    }

    /** POST /api/owner/contracts */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:active,expired,terminated'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = User::findOrFail($validated['customer_id']);

        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'The selected customer is invalid.',
            ], 422);
        }

        $unit = Unit::with('building.property')->findOrFail($validated['unit_id']);

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

        return response()->json([
            'message' => 'Contract created successfully.',
            'data' => $contract->load(['customer', 'unit']),
        ], 201);
    }

    /** GET /api/owner/contracts/{contract} */
    public function show(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeOwner($request, $contract);

        return response()->json([
            'data' => $contract->load(['customer', 'unit']),
        ]);
    }

    /** PUT /api/owner/contracts/{contract} */
    public function update(
        Request $request,
        Contract $contract
    ): JsonResponse {
        $this->authorizeOwner($request, $contract);

        $validated = $request->validate([
            'customer_id' => ['sometimes', 'integer', 'exists:users,id'],
            'unit_id' => ['sometimes', 'integer', 'exists:units,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'monthly_rent' => ['sometimes', 'numeric', 'min:0'],
            'security_deposit' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,expired,terminated'],
            'notes' => ['nullable', 'string'],
        ]);

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

        return response()->json([
            'message' => 'Contract updated successfully.',
            'data' => $contract->load(['customer', 'unit']),
        ]);
    }

    /** DELETE /api/owner/contracts/{contract} */
    public function destroy(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeOwner($request, $contract);

        $unit = $contract->unit;

        $contract->delete();

        if ($unit && $unit->status === 'occupied') {
            $unit->update([
                'status' => 'available',
            ]);
        }

        return response()->json([
            'message' => 'Contract deleted successfully.',
        ]);
    }

    private function authorizeOwner(
        Request $request,
        Contract $contract
    ): void {
        $contract->loadMissing('unit.building.property');

        abort_unless(
            $contract->unit->building->property->owner_id === $request->user()->id,
            403,
            'You are not authorized to access this contract.'
        );
    }
}