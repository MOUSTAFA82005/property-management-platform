<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StorePaymentRequest;
use App\Http\Requests\Owner\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /** GET /api/owner/payments */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Payment::class);

        $payments = Payment::with(['contract.user', 'contract.unit'])
            ->latest('due_date')
            ->paginate($request->input('per_page', 15));

        return PaymentResource::collection($payments)->response();
    }

    /** POST /api/owner/payments */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        Gate::authorize('create', Payment::class);

        $payment = Payment::create($request->validated());

        $payment->load(['contract.user', 'contract.unit']);

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/owner/payments/{payment} */
    public function show(Payment $payment): JsonResponse
    {
        Gate::authorize('view', $payment);

        $payment->load(['contract.user', 'contract.unit']);

        return (new PaymentResource($payment))->response();
    }

    /** PUT /api/owner/payments/{payment} */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        Gate::authorize('update', $payment);

        $payment->update($request->validated());

        $payment->load(['contract.user', 'contract.unit']);

        return (new PaymentResource($payment))->response();
    }

    /** DELETE /api/owner/payments/{payment} */
    public function destroy(Payment $payment): JsonResponse
    {
        Gate::authorize('delete', $payment);

        $payment->delete();

        return response()->json(null, 204);
    }
}
