<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Builder;

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
            'id',
            'id',
            'building_id',
            'property_id'
        );
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Payments do not reference a unit directly — they hang off contracts,
     * so this has to go through the contracts table.
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            Contract::class,
            'unit_id',
            'contract_id',
            'id',
            'id'
        );
    }

    /** Units belong to an owner through building → property. */
    public function scopeOwnedBy(Builder $query, User|int $owner): Builder
    {
        return $query->whereHas(
            'building.property',
            fn (Builder $q) => $q->ownedBy($owner)
        );
    }

    /** Restrict a query to units a member of the public may browse. */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereHas(
            'building.property',
            fn (Builder $q) => $q->where('is_published', true)->where('status', 'active')
        );
    }

    public function ownerId(): ?int
    {
        return $this->loadMissing('building.property')->building?->property?->owner_id;
    }
}
