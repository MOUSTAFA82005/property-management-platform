<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Return the authenticated user's data.
     * GET /api/auth/me
     *
     * The Sanctum token is the only source of identity here — no user id is
     * ever read from the request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }
}
