<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /** GET /api/owner/customers */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/owner/customers/{customer} */
    public function show(User $customer): JsonResponse
    {
        //
    }
}
