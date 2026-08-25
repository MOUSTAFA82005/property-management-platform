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
        //
    }

    /** GET /api/owner/purchase-requests/{purchaseRequest} */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        //
    }

    /** POST /api/owner/purchase-requests/{purchaseRequest}/approve */
    public function approve(PurchaseRequest $purchaseRequest): JsonResponse
    {
        //
    }

    /** POST /api/owner/purchase-requests/{purchaseRequest}/reject */
    public function reject(PurchaseRequest $purchaseRequest): JsonResponse
    {
        //
    }
}
