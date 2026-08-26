<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $property = $this->relationLoaded('property')
            ? $this->property
            : ($this->relationLoaded('building') && $this->building ? $this->building->property : null);

        $propertyId = $property?->id ?? $this->building?->property_id;

        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'property_id' => $propertyId,
            'property_name' => $property?->name,
            'unit_number' => $this->unit_number,
            'floor' => $this->floor,
            'unit_type' => $this->unit_type,
            'area' => $this->area ? (float) $this->area : null,
            'bedrooms' => (int) $this->bedrooms,
            'bathrooms' => (int) $this->bathrooms,
            'monthly_rent' => (float) $this->monthly_rent,
            'status' => $this->status,

            'building' => $this->whenLoaded('building', function () {
                return [
                    'id' => $this->building->id,
                    'name' => $this->building->name,
                    'floors_count' => $this->building->floors_count,
                    'description' => $this->building->description,
                ];
            }),

            'property' => $this->whenLoaded('property', function () {
                return [
                    'id' => $this->property->id,
                    'name' => $this->property->name,
                    'city' => $this->property->city,
                    'address' => $this->property->address,
                ];
            }),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}