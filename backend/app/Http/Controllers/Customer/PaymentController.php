<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** GET /api/payments  — customer's own payments */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/payments/{payment}  — view payment details */
    public function show(Payment $payment): JsonResponse
    {
        //
    }
}
