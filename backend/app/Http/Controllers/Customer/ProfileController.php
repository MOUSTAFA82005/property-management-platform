<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     *
     * Always the token holder — no id is accepted from the caller, so one user
     * can never read another's profile.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /** PUT /api/profile */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        // Only these keys are ever written. `role` and `status` are not
        // validated and not copied, so a customer cannot promote themselves
        // to owner by adding fields to the request body.
        $updates = $request->safe()->only(['name', 'email', 'phone']);

        if ($request->filled('password')) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['That is not your current password.'],
                ]);
            }

            $updates['password'] = $request->validated('password');
        }

        $user->update($updates);

        return response()->json([
            'message' => 'Profile updated.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }
}
