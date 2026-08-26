<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Return summary statistics for the owner.
     * GET /api/owner/dashboard
     */
    public function __invoke(Request $request): JsonResponse
    {
        //
    }
}
