<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /** GET /api/payments  — customer's own payments via their contracts */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Payment::class);

        $payments = $request->user()
            ->payments()
            ->with('contract.unit.building.property')
            ->latest('due_date')
            ->get();

        return PaymentResource::collection($payments)->response();
    }

    /** GET /api/payments/{payment}  — view payment details */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        $payment->load('contract.unit.building.property');

        Gate::authorize('view', $payment);

        return (new PaymentResource($payment))->response();
    }
}
