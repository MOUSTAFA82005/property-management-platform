<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'customer_id',
        'unit_id',
        'status',
        'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** Requests reach an owner through unit → building → property. */
    public function scopeOwnedBy(Builder $query, User|int $owner): Builder
    {
        return $query->whereHas(
            'unit.building.property',
            fn (Builder $q) => $q->ownedBy($owner)
        );
    }

    public function ownerId(): ?int
    {
        return $this->loadMissing('unit.building.property')
            ->unit?->building?->property?->owner_id;
    }
}
