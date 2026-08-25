<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'contract_id'    => $this->contract_id,
            'amount'         => $this->amount,
            'due_date'       => $this->due_date,
            'paid_date'      => $this->paid_date,
            'payment_method' => $this->payment_method,
            'status'         => $this->status,
            'reference'      => $this->reference,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'contract'       => new ContractResource($this->whenLoaded('contract')),
        ];
    }
}
