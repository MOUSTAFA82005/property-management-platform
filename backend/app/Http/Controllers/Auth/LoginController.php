<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Authenticate a user (owner or customer) and return a token.
     * POST /api/auth/login
     */
    public function __invoke(Request $request): JsonResponse
    {
        //
    }
}
