<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'customer'], true);
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === 'customer') {
            return $payment->contract?->user_id === $user->id;
        }

        return $user->role === 'owner';
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->role === 'owner';
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->role === 'owner';
    }
}
