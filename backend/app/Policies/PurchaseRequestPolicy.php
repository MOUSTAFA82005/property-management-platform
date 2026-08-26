<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    /** Any authenticated user whose role allows listing purchase requests. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'customer'], true);
    }

    /** Owners can view any request. Customers can only view their own. */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        return $user->role === 'customer' && $purchaseRequest->customer_id === $user->id;
    }

    /** Only customers may create purchase requests. */
    public function create(User $user): bool
    {
        return $user->role === 'customer';
    }

    /** Only the owning customer may cancel their own request. */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->role === 'customer' && $purchaseRequest->customer_id === $user->id;
    }

    /** Only owners may approve a purchase request. */
    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->role === 'owner';
    }

    /** Only owners may reject a purchase request. */
    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->role === 'owner';
    }
}
