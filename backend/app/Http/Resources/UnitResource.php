<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'building_id'   => $this->building_id,
            'unit_number'   => $this->unit_number,
            'floor'         => $this->floor,
            'unit_type'     => $this->unit_type,
            'area'          => $this->area,
            'bedrooms'      => $this->bedrooms,
            'bathrooms'     => $this->bathrooms,
            'monthly_rent'  => $this->monthly_rent,
            'status'        => $this->status,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
