<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Policies\Concerns\ChecksPropertyOwnership;

class PropertyPolicy
{
    use ChecksPropertyOwnership;

    public function viewAny(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function view(User $user, Property $property): bool
    {
        return $this->owns($user, $property);
    }

    public function create(User $user): bool
    {
        return $this->isOwner($user);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->owns($user, $property);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->owns($user, $property);
    }

    public function publish(User $user, Property $property): bool
    {
        return $this->owns($user, $property);
    }

    public function unpublish(User $user, Property $property): bool
    {
        return $this->owns($user, $property);
    }
}
