<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\User;

/**
 * Raised when an owner creates, edits or deletes a contract.
 *
 * The customer on the lease is the one told — they are the party whose
 * agreement just changed and who cannot see it happen otherwise.
 */
class ContractNotification extends ActivityNotification
{
    public const CREATED = 'contract.created';
    public const UPDATED = 'contract.updated';
    public const DELETED = 'contract.deleted';

    public function __construct(
        private readonly string $event,
        private readonly int $contractId,
        private readonly ?string $unitNumber = null,
        private readonly ?string $status = null,
    ) {
    }

    public static function created(Contract $contract): self
    {
        return new self(self::CREATED, $contract->id, $contract->unit?->unit_number, $contract->status);
    }

    public static function updated(Contract $contract): self
    {
        return new self(self::UPDATED, $contract->id, $contract->unit?->unit_number, $contract->status);
    }

    /**
     * Deletion is built from values read before the row went, because the
     * record no longer exists by the time this is sent.
     */
    public static function deleted(int $contractId, ?string $unitNumber): self
    {
        return new self(self::DELETED, $contractId, $unitNumber);
    }

    /** @return array<string, mixed> */
    protected function payload(User $notifiable): array
    {
        $reference = 'CTR-'.str_pad((string) $this->contractId, 4, '0', STR_PAD_LEFT);
        $unit = $this->unitNumber ? " for unit {$this->unitNumber}" : '';

        return [
            'type' => $this->event,
            'title' => match ($this->event) {
                self::CREATED => 'New contract',
                self::UPDATED => 'Contract updated',
                self::DELETED => 'Contract removed',
            },
            'message' => match ($this->event) {
                self::CREATED => "Contract {$reference}{$unit} has been created.",
                self::UPDATED => "Contract {$reference}{$unit} was updated"
                    .($this->status ? " and is now {$this->status}." : '.'),
                self::DELETED => "Contract {$reference}{$unit} has been removed.",
            },
            // A deleted contract has no detail page left to open.
            'url' => $this->event === self::DELETED
                ? $this->routeFor($notifiable, '/owner/contracts', '/contracts')
                : $this->routeFor($notifiable, "/owner/contracts/{$this->contractId}", "/contracts/{$this->contractId}"),
        ];
    }
}
