<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Everything past `status` is conditional, so the auth endpoints keep
     * returning exactly the same small payload they always did.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'role'   => $this->role,
            'status' => $this->status,

            'contracts_count'         => $this->whenCounted('contracts'),
            'purchase_requests_count' => $this->whenCounted('purchaseRequests'),

            'contracts'         => ContractResource::collection($this->whenLoaded('contracts')),
            'purchase_requests' => PurchaseRequestResource::collection($this->whenLoaded('purchaseRequests')),

            'created_at' => $this->whenNotNull($this->created_at?->toIso8601String()),
        ];
    }
}
