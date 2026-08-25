<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /** GET /api/contracts  — customer's own contracts */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/contracts/{contract}  — view contract details */
    public function show(Contract $contract): JsonResponse
    {
        //
    }
}
