<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Register a new account and issue a Sanctum token.
     * POST /api/auth/register
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'phone'    => $request->validated('phone'),
            // Never taken raw from the request — RegisterRequest whitelists it.
            'role'     => $request->resolvedRole(),
            'status'   => 'active',
            // The `hashed` cast on User::casts() applies bcrypt on write.
            'password' => $request->validated('password'),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user'    => new UserResource($user),
            'token'   => $token,
        ], 201);
    }
}
