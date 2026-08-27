<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Contract extends Model
{
    protected $fillable = [
        'user_id',
        'unit_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'security_deposit',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'monthly_rent'     => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Contracts belong to an owner through unit → building → property. */
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
