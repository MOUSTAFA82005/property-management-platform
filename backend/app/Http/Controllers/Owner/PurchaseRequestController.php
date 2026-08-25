<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    /** GET /api/owner/purchase-requests */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        // TODO: return PurchaseRequest::with('customer', 'unit.property')->paginate(15);
    }

    /** GET /api/owner/purchase-requests/{purchaseRequest} */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('view', $purchaseRequest);

        // TODO: return response()->json($purchaseRequest->load('customer', 'unit.property'));
    }

    /** POST /api/owner/purchase-requests/{purchaseRequest}/approve */
    public function approve(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('approve', $purchaseRequest);

        // TODO: implement approval logic
    }

    /** POST /api/owner/purchase-requests/{purchaseRequest}/reject */
    public function reject(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('reject', $purchaseRequest);

        // TODO: implement rejection logic
    }
}
