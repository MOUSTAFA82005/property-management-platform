<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /** GET /api/owner/contracts */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /** POST /api/owner/contracts */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /** GET /api/owner/contracts/{contract} */
    public function show(Contract $contract): JsonResponse
    {
        //
    }

    /** PUT /api/owner/contracts/{contract} */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        //
    }

    /** DELETE /api/owner/contracts/{contract} */
    public function destroy(Contract $contract): JsonResponse
    {
        //
    }
}
