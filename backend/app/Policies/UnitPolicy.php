<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use App\Policies\Concerns\ChecksPropertyOwnership;

class UnitPolicy
{
    use ChecksPropertyOwnership;

    public function viewAny(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->owns($user, $unit);
    }

    public function create(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->owns($user, $unit);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->owns($user, $unit);
    }
}
