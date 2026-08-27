<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksPropertyOwnership;

class PaymentPolicy
{
    use ChecksPropertyOwnership;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'customer'], true);
    }

    /**
     * A customer sees payments on their own contracts; an owner sees payments
     * collected against units in their own properties.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($this->isCustomer($user)) {
            return $payment->customerId() === $user->id;
        }

        return $this->owns($user, $payment);
    }

    public function create(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->owns($user, $payment);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->owns($user, $payment);
    }
}
