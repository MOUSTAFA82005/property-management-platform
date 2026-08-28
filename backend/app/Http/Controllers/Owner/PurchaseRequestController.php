<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use App\Notifications\PurchaseRequestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestController extends Controller
{
    /** GET /api/owner/purchase-requests */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PurchaseRequest::class);

        $requests = PurchaseRequest::query()
            ->ownedBy($request->user())
            ->with(['customer', 'unit.building.property'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q
                    ->whereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', $term)));
            })
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15));

        return PurchaseRequestResource::collection($requests)->response();
    }

    /** GET /api/owner/purchase-requests/{purchaseRequest} */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        Gate::authorize('view', $purchaseRequest);

        $purchaseRequest->load(['customer', 'unit.building.property']);

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * POST /api/owner/purchase-requests/{purchaseRequest}/approve
     *
     * Approving reserves the unit, so it is only valid from `pending` and only
     * while the unit is still available.
     */
    public function approve(PurchaseRequest $purchaseRequest): JsonResponse
    {
        Gate::authorize('approve', $purchaseRequest);

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'message' => "This request has already been {$purchaseRequest->status} and cannot be approved.",
            ], 422);
        }

        $purchaseRequest->loadMissing('unit');

        if ($purchaseRequest->unit?->status !== 'available') {
            return response()->json([
                'message' => 'That unit is no longer available, so this request cannot be approved.',
            ], 422);
        }

        DB::transaction(function () use ($purchaseRequest) {
            $purchaseRequest->update(['status' => 'approved']);
            $purchaseRequest->unit->update(['status' => 'reserved']);
        });

        $purchaseRequest->load(['customer', 'unit.building.property']);

        $purchaseRequest->customer?->notify(PurchaseRequestNotification::approved($purchaseRequest));

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * POST /api/owner/purchase-requests/{purchaseRequest}/reject
     *
     * Rejecting leaves the unit exactly as it was.
     */
    public function reject(PurchaseRequest $purchaseRequest): JsonResponse
    {
        Gate::authorize('reject', $purchaseRequest);

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'message' => "This request has already been {$purchaseRequest->status} and cannot be rejected.",
            ], 422);
        }

        $purchaseRequest->update(['status' => 'rejected']);

        $purchaseRequest->load(['customer', 'unit.building.property']);

        $purchaseRequest->customer?->notify(PurchaseRequestNotification::rejected($purchaseRequest));

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }
}
