<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StorePurchaseRequestRequest;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    /** GET /api/purchase-requests  — customer's own requests */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        // TODO: return auth()->user()->purchaseRequests()->with('unit.property')->paginate(15);
    }

    /** POST /api/purchase-requests  — submit a new request */
    public function store(StorePurchaseRequestRequest $request): JsonResponse
    {
        // Authorization is enforced by StorePurchaseRequestRequest::authorize()
        // which requires role === 'customer'. Policy::create() provides a second layer.
        $this->authorize('create', PurchaseRequest::class);

        // TODO: implement purchase request creation
    }

    /** GET /api/purchase-requests/{purchaseRequest}  — track a request */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('view', $purchaseRequest);

        // TODO: return response()->json($purchaseRequest->load('unit.property'));
    }

    /** DELETE /api/purchase-requests/{purchaseRequest}  — cancel a request */
    public function destroy(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('delete', $purchaseRequest);

        // TODO: implement cancellation logic
    }
}
