<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StorePurchaseRequestRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use App\Notifications\PurchaseRequestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestController extends Controller
{
    /** GET /api/purchase-requests — the customer's own requests. */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PurchaseRequest::class);

        $requests = PurchaseRequest::query()
            ->where('customer_id', $request->user()->id)
            ->with('unit.building.property')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return PurchaseRequestResource::collection($requests)->response();
    }

    /** POST /api/purchase-requests — submit a new request. */
    public function store(StorePurchaseRequestRequest $request): JsonResponse
    {
        Gate::authorize('create', PurchaseRequest::class);

        $unit = Unit::findOrFail($request->validated('unit_id'));

        // A request may only be raised against a unit the customer can
        // actually see in the public catalog.
        abort_unless(
            Unit::query()->publiclyVisible()->whereKey($unit->id)->exists(),
            404,
            'Unit not found.'
        );

        if ($unit->status !== 'available') {
            return response()->json([
                'message' => 'That unit is not currently available.',
            ], 422);
        }

        $alreadyOpen = PurchaseRequest::query()
            ->where('customer_id', $request->user()->id)
            ->where('unit_id', $unit->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($alreadyOpen) {
            return response()->json([
                'message' => 'You already have an open request for this unit.',
            ], 422);
        }

        $purchaseRequest = PurchaseRequest::create([
            // customer_id comes from the token, never the request body.
            'customer_id' => $request->user()->id,
            'unit_id'     => $unit->id,
            'status'      => 'pending',
            'notes'       => $request->validated('notes'),
        ]);

        $purchaseRequest->load('unit.building.property');

        // The other direction: the owner of the unit is the one who has to
        // act on this, so they are the one told.
        $purchaseRequest->unit?->building?->property?->owner?->notify(
            PurchaseRequestNotification::submitted($purchaseRequest, $request->user()->name),
        );

        return (new PurchaseRequestResource($purchaseRequest))->response()->setStatusCode(201);
    }

    /** GET /api/purchase-requests/{purchaseRequest} — track a request. */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        Gate::authorize('view', $purchaseRequest);

        $purchaseRequest->load('unit.building.property');

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * DELETE /api/purchase-requests/{purchaseRequest} — cancel a request.
     *
     * Cancelling an approved request releases the reservation it created,
     * otherwise the unit would stay reserved for a request nobody holds.
     */
    public function destroy(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        Gate::authorize('delete', $purchaseRequest);

        if (! in_array($purchaseRequest->status, ['pending', 'approved'], true)) {
            return response()->json([
                'message' => "This request has already been {$purchaseRequest->status}.",
            ], 422);
        }

        $wasApproved = $purchaseRequest->status === 'approved';

        DB::transaction(function () use ($purchaseRequest, $wasApproved) {
            $purchaseRequest->update(['status' => 'cancelled']);

            if (! $wasApproved) {
                return;
            }

            $unit = $purchaseRequest->unit()->first();

            if (! $unit || $unit->status !== 'reserved') {
                return;
            }

            $stillReserved = PurchaseRequest::query()
                ->where('unit_id', $unit->id)
                ->where('id', '!=', $purchaseRequest->id)
                ->where('status', 'approved')
                ->exists();

            if (! $stillReserved) {
                $unit->update(['status' => 'available']);
            }
        });

        $purchaseRequest->load('unit.building.property');

        $purchaseRequest->unit?->building?->property?->owner?->notify(
            PurchaseRequestNotification::cancelled($purchaseRequest, $request->user()->name),
        );

        return (new PurchaseRequestResource($purchaseRequest->fresh()))->response();
    }
}
