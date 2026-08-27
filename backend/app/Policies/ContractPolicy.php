<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'customer'], true);
    }

    public function view(User $user, Contract $contract): bool
    {
        if ($user->role === 'customer') {
            return $contract->user_id === $user->id;
        }

        return $user->role === 'owner';
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->role === 'owner';
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->role === 'owner';
    }
}
