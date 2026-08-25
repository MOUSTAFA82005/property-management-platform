<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Revoke the current access token.
     * POST /api/auth/logout
     */
    public function __invoke(Request $request): JsonResponse
    {
        //
    }
}
