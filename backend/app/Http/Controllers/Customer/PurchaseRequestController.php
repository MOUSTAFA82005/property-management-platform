<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    /** GET /api/purchase-requests  — customer's own requests */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** POST /api/purchase-requests  — submit a new request */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/purchase-requests/{purchaseRequest}  — track a request */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        //
    }

    /** DELETE /api/purchase-requests/{purchaseRequest}  — cancel a request */
    public function destroy(PurchaseRequest $purchaseRequest): JsonResponse
    {
        //
    }
}
