<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Payment extends Model
{
    protected $fillable = [
        'contract_id',
        'amount',
        'due_date',
        'paid_date',
        'payment_method',
        'status',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'due_date'  => 'date',
        'paid_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /** Payments belong to an owner through contract → unit → building → property. */
    public function scopeOwnedBy(Builder $query, User|int $owner): Builder
    {
        return $query->whereHas(
            'contract.unit.building.property',
            fn (Builder $q) => $q->ownedBy($owner)
        );
    }

    public function ownerId(): ?int
    {
        return $this->loadMissing('contract.unit.building.property')
            ->contract?->unit?->building?->property?->owner_id;
    }

    /** The customer responsible for this payment. */
    public function customerId(): ?int
    {
        return $this->loadMissing('contract')->contract?->user_id;
    }
}
