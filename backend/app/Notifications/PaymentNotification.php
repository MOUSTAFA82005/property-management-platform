<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;

/**
 * Raised when an owner records a payment or changes its status.
 *
 * The customer on the contract is told: this is money they owe or have been
 * credited with, and the payment list is the only place it shows up.
 */
class PaymentNotification extends ActivityNotification
{
    public const RECORDED = 'payment.recorded';
    public const UPDATED = 'payment.updated';

    public function __construct(
        private readonly string $event,
        private readonly int $paymentId,
        private readonly ?string $reference,
        private readonly ?string $status,
    ) {
    }

    public static function recorded(Payment $payment): self
    {
        return new self(self::RECORDED, $payment->id, $payment->reference, $payment->status);
    }

    public static function updated(Payment $payment): self
    {
        return new self(self::UPDATED, $payment->id, $payment->reference, $payment->status);
    }

    /** @return array<string, mixed> */
    protected function payload(User $notifiable): array
    {
        $label = $this->reference ?: 'PAY-'.str_pad((string) $this->paymentId, 4, '0', STR_PAD_LEFT);

        return [
            'type' => $this->event,
            'title' => $this->event === self::RECORDED ? 'Payment recorded' : 'Payment updated',
            'message' => $this->event === self::RECORDED
                ? "Payment {$label} has been added to your schedule."
                : "Payment {$label} is now marked {$this->status}.",
            'url' => $this->routeFor(
                $notifiable,
                '/owner/payments',
                "/payments/{$this->paymentId}",
            ),
        ];
    }
}
