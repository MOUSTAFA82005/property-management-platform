<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** GET /api/owner/payments */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** POST /api/owner/payments */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/owner/payments/{payment} */
    public function show(Payment $payment): JsonResponse
    {
        //
    }

    /** PUT /api/owner/payments/{payment} */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        //
    }

    /** DELETE /api/owner/payments/{payment} */
    public function destroy(Payment $payment): JsonResponse
    {
        //
    }
}
