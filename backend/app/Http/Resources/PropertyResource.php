<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $unitsCollection = $this->relationLoaded('units') ? $this->units : $this->units()->get();
        $buildingsCount = $this->relationLoaded('buildings') ? $this->buildings->count() : $this->buildings()->count();
        $totalUnits = $unitsCollection->count();
        $availableUnits = $unitsCollection->where('status', 'available')->count();
        $minRent = $unitsCollection->min('monthly_rent') ?? 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'description' => $this->description,
            'property_type' => $this->property_type,
            'status' => $this->status,
            'manager_id' => $this->manager_id,
            'buildings_count' => max(1, $buildingsCount),
            'units_count' => $totalUnits,
            'available_units_count' => $availableUnits,
            'from_price' => (float) $minRent,
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->name,
                    'email' => $this->manager->email,
                ];
            }),
            'units' => UnitResource::collection($this->whenLoaded('units')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}