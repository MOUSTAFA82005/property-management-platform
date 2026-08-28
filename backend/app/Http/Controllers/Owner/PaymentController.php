<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StorePaymentRequest;
use App\Http\Requests\Owner\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Contract;
use App\Models\Payment;
use App\Notifications\PaymentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /** GET /api/owner/payments */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Payment::class);

        // Previously unscoped: every owner saw every payment in the system.
        $payments = Payment::query()
            ->ownedBy($request->user())
            ->with(['contract.user', 'contract.unit'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('contract_id'), fn ($q) => $q->where('contract_id', $request->integer('contract_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q
                    ->where('reference', 'like', $term)
                    ->orWhere('payment_method', 'like', $term)
                    ->orWhereHas('contract.user', fn ($u) => $u->where('name', 'like', $term))
                    ->orWhereHas('contract.unit', fn ($u) => $u->where('unit_number', 'like', $term)));
            })
            ->latest('due_date')
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15));

        return PaymentResource::collection($payments)->response();
    }

    /** POST /api/owner/payments */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        Gate::authorize('create', Payment::class);

        $contract = Contract::findOrFail($request->validated('contract_id'));

        // A payment may only be raised against a contract on your own unit.
        Gate::authorize('update', $contract);

        $payment = Payment::create($request->validated());

        $payment->load(['contract.user', 'contract.unit']);

        // The customer on the contract owes or has been credited this money.
        $payment->contract?->user?->notify(PaymentNotification::recorded($payment));

        return (new PaymentResource($payment))->response()->setStatusCode(201);
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

        // Re-pointing a payment at a different contract must not be a way to
        // reach another owner's records.
        if ($request->filled('contract_id')) {
            Gate::authorize('update', Contract::findOrFail($request->validated('contract_id')));
        }

        $statusBefore = $payment->status;

        $payment->update($request->validated());

        $payment->load(['contract.user', 'contract.unit']);

        // Only a real status transition is worth interrupting someone for;
        // correcting a typo in the notes is not.
        if ($payment->status !== $statusBefore) {
            $payment->contract?->user?->notify(PaymentNotification::updated($payment));
        }

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
