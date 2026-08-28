<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'property_id'  => $this->property_id,
            'name'         => $this->name,
            'floors_count' => (int) $this->floors_count,
            'description'  => $this->description,
            'units_count'  => $this->whenCounted('units'),
            'property'     => $this->whenLoaded('property', fn () => [
                'id'   => $this->property->id,
                'name' => $this->property->name,
                'city' => $this->property->city,
            ]),
            'units'        => UnitResource::collection($this->whenLoaded('units')),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
