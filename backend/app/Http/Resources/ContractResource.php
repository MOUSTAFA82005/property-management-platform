<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'unit_id'          => $this->unit_id,
            'start_date'       => $this->start_date,
            'end_date'         => $this->end_date,
            'monthly_rent'     => $this->monthly_rent,
            'security_deposit' => $this->security_deposit,
            'status'           => $this->status,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'user'             => new UserResource($this->whenLoaded('user')),
            'unit'             => new UnitResource($this->whenLoaded('unit')),
            'payments'         => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
