<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Authenticate a user (owner or customer) and return a token.
     * POST /api/auth/login
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        // One response for "no such user" and "wrong password" so the endpoint
        // cannot be used to enumerate registered email addresses.
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user'    => new UserResource($user),
            'token'   => $token,
        ]);
    }
}
