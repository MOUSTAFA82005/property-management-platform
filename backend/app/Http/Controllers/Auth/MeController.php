<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Return the authenticated user's data.
     * GET /api/auth/me
     */
    public function __invoke(Request $request): JsonResponse
    {
        //
    }
}
