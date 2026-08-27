<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Shared ownership predicate for every record that hangs off a property.
 *
 * The ownership chain itself lives on the models (ownerId()); this only
 * answers the policy question, so no policy has to walk relationships by hand.
 */
trait ChecksPropertyOwnership
{
    protected function isOwner(User $user): bool
    {
        return $user->role === 'owner';
    }

    protected function isCustomer(User $user): bool
    {
        return $user->role === 'customer';
    }

    /**
     * True only when the user is an owner AND the record belongs to them.
     *
     * Being an owner is never sufficient on its own — that was the hole this
     * trait exists to close.
     */
    protected function owns(User $user, object $record): bool
    {
        if (! $this->isOwner($user)) {
            return false;
        }

        $ownerId = $record->ownerId();

        return $ownerId !== null && $ownerId === $user->id;
    }
}
