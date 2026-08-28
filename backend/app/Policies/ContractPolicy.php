<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use App\Policies\Concerns\ChecksPropertyOwnership;

class ContractPolicy
{
    use ChecksPropertyOwnership;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'customer'], true);
    }

    /**
     * Owners see contracts on their own units; customers see their own
     * contracts. Nobody sees anything else.
     */
    public function view(User $user, Contract $contract): bool
    {
        if ($this->isCustomer($user)) {
            return $contract->user_id === $user->id;
        }

        return $this->owns($user, $contract);
    }

    public function create(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function update(User $user, Contract $contract): bool
    {
        return $this->owns($user, $contract);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $this->owns($user, $contract);
    }
}
