<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksPropertyOwnership;

class PurchaseRequestPolicy
{
    use ChecksPropertyOwnership;

    /** Any authenticated user whose role allows listing purchase requests. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'customer'], true);
    }

    /**
     * Customers see their own requests; owners see requests raised against
     * units in their own properties.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if ($this->isCustomer($user)) {
            return $purchaseRequest->customer_id === $user->id;
        }

        return $this->owns($user, $purchaseRequest);
    }

    /** Only customers may create purchase requests. */
    public function create(User $user): bool
    {
        return $this->isCustomer($user);
    }

    /** Only the requesting customer may cancel their own request. */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->isCustomer($user) && $purchaseRequest->customer_id === $user->id;
    }

    /** Only the owner of the requested unit may approve it. */
    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->owns($user, $purchaseRequest);
    }

    /** Only the owner of the requested unit may reject it. */
    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->owns($user, $purchaseRequest);
    }
}
