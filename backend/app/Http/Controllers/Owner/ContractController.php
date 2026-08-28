<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Unit;
use App\Notifications\ContractNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->orderBy('id')
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

        // A free unit can always be let. A reserved one can only be let to the
        // customer whose approved purchase request reserved it — approval is
        // the step this contract is being written from, so refusing it here
        // would leave an approved request with no way to become a contract.
        if (! $this->unitIsLettableTo($unit, $customer)) {
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

        // The customer is told about their own lease: they cannot see the
        // owner create it any other way.
        $customer->notify(ContractNotification::created($contract));

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

        $customer = isset($validated['user_id'])
            ? User::findOrFail($validated['user_id'])
            : null;

        if ($customer && $customer->role !== 'customer') {
            return response()->json([
                'message' => 'The selected customer is invalid.',
            ], 422);
        }

        $movingUnit = isset($validated['unit_id'])
            && (int) $validated['unit_id'] !== $contract->unit_id;

        $previousUnit = $movingUnit ? $contract->unit : null;
        $targetUnit = null;

        if (isset($validated['unit_id'])) {
            $targetUnit = Unit::with('building.property')
                ->findOrFail($validated['unit_id']);

            if ($targetUnit->building->property->owner_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'You are not authorized to use this unit.',
                ], 403);
            }
        }

        // Moving a contract onto a different unit has to clear the same bar as
        // writing a new one, or an edit would be a way to let a unit that is
        // already taken. Staying on the same unit is always fine.
        if ($movingUnit) {
            // Whoever will hold the lease once this update lands.
            if (! $this->unitIsLettableTo($targetUnit, $customer ?? $contract->user)) {
                return response()->json([
                    'message' => 'This unit is not available.',
                ], 422);
            }
        }

        DB::transaction(function () use ($contract, $validated, $movingUnit, $previousUnit, $targetUnit) {
            $contract->update($validated);

            if (! $movingUnit) {
                return;
            }

            $targetUnit->update(['status' => 'occupied']);

            // The unit it came from is only free again if nothing else still
            // holds it — a unit can carry more than one contract over time.
            $this->releaseUnit($previousUnit, $contract->id);
        });

        $contract->load([
            'user',
            'unit.building.property',
            'payments',
        ]);

        $contract->user?->notify(ContractNotification::updated($contract));

        return (new ContractResource($contract))->response();
    }

    /** DELETE /api/owner/contracts/{contract} */
    public function destroy(Contract $contract): JsonResponse
    {
        Gate::authorize('delete', $contract);

        // Payments are restricted on delete for good reason — the collection
        // history has to outlive the contract. Refusing here gives a clear
        // message instead of a database-level failure, and matches how a
        // property with dependent records already behaves.
        if ($contract->payments()->exists()) {
            return response()->json([
                'message' => 'This contract still has payments recorded against it. Remove those payments first.',
            ], 409);
        }

        $unit = $contract->unit;

        // Captured before the row goes: the notification describes a record
        // that will no longer exist.
        $customer = $contract->user;
        $contractId = $contract->id;
        $unitNumber = $unit?->unit_number;

        DB::transaction(function () use ($contract, $unit) {
            $contract->delete();

            $this->releaseUnit($unit, $contract->id);
        });

        $customer?->notify(ContractNotification::deleted($contractId, $unitNumber));

        return response()->json(null, 204);
    }

    /**
     * Hand an occupied unit back to the pool, unless another live contract
     * still holds it.
     *
     * A unit accumulates contracts over time — the seeded B-102 has an
     * expired one behind its active one — so "this contract is gone" is not
     * on its own a reason to advertise the unit as free.
     */
    private function releaseUnit(?Unit $unit, int $excludingContractId): void
    {
        if (! $unit || $unit->status !== 'occupied') {
            return;
        }

        $stillLet = Contract::query()
            ->where('unit_id', $unit->id)
            ->where('id', '!=', $excludingContractId)
            ->where('status', 'active')
            ->exists();

        if (! $stillLet) {
            $unit->update(['status' => 'available']);
        }
    }

    /**
     * Whether a contract may be written against this unit for this customer.
     *
     * `available` is the ordinary case. `reserved` is the purchase-request
     * case: the unit was reserved when the owner approved that customer's
     * request, and the contract is the next step in the same flow.
     */
    private function unitIsLettableTo(Unit $unit, User $customer): bool
    {
        if ($unit->status === 'available') {
            return true;
        }

        if ($unit->status !== 'reserved') {
            return false;
        }

        return PurchaseRequest::query()
            ->where('unit_id', $unit->id)
            ->where('customer_id', $customer->id)
            ->where('status', 'approved')
            ->exists();
    }
}
