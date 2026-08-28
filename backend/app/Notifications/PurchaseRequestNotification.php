<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Models\User;

/**
 * The two-way half of the system.
 *
 * A customer submitting or cancelling a request notifies the owner of the
 * unit; an owner approving or rejecting one notifies the customer who raised
 * it. Both directions are real transitions the controllers already perform.
 */
class PurchaseRequestNotification extends ActivityNotification
{
    public const SUBMITTED = 'purchase_request.submitted';
    public const CANCELLED = 'purchase_request.cancelled';
    public const APPROVED = 'purchase_request.approved';
    public const REJECTED = 'purchase_request.rejected';

    public function __construct(
        private readonly string $event,
        private readonly int $requestId,
        private readonly ?string $unitNumber,
        private readonly ?string $customerName = null,
    ) {
    }

    public static function submitted(PurchaseRequest $request, string $customerName): self
    {
        return new self(self::SUBMITTED, $request->id, $request->unit?->unit_number, $customerName);
    }

    public static function cancelled(PurchaseRequest $request, string $customerName): self
    {
        return new self(self::CANCELLED, $request->id, $request->unit?->unit_number, $customerName);
    }

    public static function approved(PurchaseRequest $request): self
    {
        return new self(self::APPROVED, $request->id, $request->unit?->unit_number);
    }

    public static function rejected(PurchaseRequest $request): self
    {
        return new self(self::REJECTED, $request->id, $request->unit?->unit_number);
    }

    /** @return array<string, mixed> */
    protected function payload(User $notifiable): array
    {
        $unit = $this->unitNumber ? "unit {$this->unitNumber}" : 'a unit';
        $who = $this->customerName ?: 'A customer';

        return [
            'type' => $this->event,
            'title' => match ($this->event) {
                self::SUBMITTED => 'New purchase request',
                self::CANCELLED => 'Purchase request cancelled',
                self::APPROVED => 'Request approved',
                self::REJECTED => 'Request declined',
            },
            'message' => match ($this->event) {
                self::SUBMITTED => "{$who} has requested {$unit}.",
                self::CANCELLED => "{$who} withdrew their request for {$unit}.",
                self::APPROVED => "Your request for {$unit} was approved and the unit is reserved for you.",
                self::REJECTED => "Your request for {$unit} was declined.",
            },
            'url' => $this->routeFor(
                $notifiable,
                "/owner/purchase-requests/{$this->requestId}",
                "/purchase-requests/{$this->requestId}",
            ),
        ];
    }
}
