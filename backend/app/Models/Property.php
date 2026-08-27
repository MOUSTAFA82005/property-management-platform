<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'city',
        'description',
        'property_type',
        'status',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(
            Unit::class,
            Building::class,
            'property_id',
            'building_id',
            'id',
            'id'
        );
    }

    /**
     * Restrict a query to properties belonging to one owner.
     *
     * Ownership of every other record in the system is derived from this
     * relationship, so the chain is defined once per model and reused by the
     * policies and the owner-facing controllers rather than being re-written
     * inline wherever it happens to be needed.
     */
    public function scopeOwnedBy(Builder $query, User|int $owner): Builder
    {
        return $query->where('owner_id', $owner instanceof User ? $owner->id : $owner);
    }

    /** The id of the owner this record ultimately belongs to. */
    public function ownerId(): ?int
    {
        return $this->owner_id;
    }
}
