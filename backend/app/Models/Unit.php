<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'unit_number',
        'floor',
        'unit_type',
        'area',
        'bedrooms',
        'bathrooms',
        'monthly_rent',
        'status',
    ];

    protected $casts = [
        'floor' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'area' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function property(): HasOneThrough
    {
        return $this->hasOneThrough(
            Property::class,
            Building::class,
            'id',          // Foreign key on buildings table (local key in hasOneThrough context)
            'id',          // Foreign key on properties table
            'building_id', // Local key on units table
            'property_id'  // Local key on buildings table
        );
    }
}