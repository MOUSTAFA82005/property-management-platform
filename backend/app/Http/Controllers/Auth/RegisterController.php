<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Register a new customer account.
     * POST /api/auth/register
     */
    public function __invoke(Request $request): JsonResponse
    {
        //
    }
}
