<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'customer_id' => $this->customer_id,
            'unit_id'     => $this->unit_id,
            'status'      => $this->status,
            'notes'       => $this->notes,
            'customer'    => new UserResource($this->whenLoaded('customer')),
            'unit'        => new UnitResource($this->whenLoaded('unit')),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
