<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;
use App\Policies\Concerns\ChecksPropertyOwnership;

class BuildingPolicy
{
    use ChecksPropertyOwnership;

    public function viewAny(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function view(User $user, Building $building): bool
    {
        return $this->owns($user, $building);
    }

    public function create(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function update(User $user, Building $building): bool
    {
        return $this->owns($user, $building);
    }

    public function delete(User $user, Building $building): bool
    {
        return $this->owns($user, $building);
    }
}
