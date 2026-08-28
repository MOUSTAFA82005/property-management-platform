<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'floors_count',
        'description',
    ];

    protected $casts = [
        'floors_count' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /** Buildings belong to an owner through their property. */
    public function scopeOwnedBy(Builder $query, User|int $owner): Builder
    {
        return $query->whereHas(
            'property',
            fn (Builder $q) => $q->ownedBy($owner)
        );
    }

    public function ownerId(): ?int
    {
        return $this->loadMissing('property')->property?->owner_id;
    }
}
