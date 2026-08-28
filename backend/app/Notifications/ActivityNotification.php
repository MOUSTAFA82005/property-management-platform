<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Base for every PropSpace notification.
 *
 * All of them go to the database channel only — there is no mail or broadcast
 * configuration in this project, and adding one would be inventing
 * infrastructure. They are deliberately *not* queued: the app runs on the
 * `database` queue driver with no worker in development, so a queued
 * notification would simply never arrive.
 *
 * Every payload has the same shape so the frontend can render any
 * notification without knowing its class:
 *
 *   type     machine-readable event key, e.g. `contract.created`
 *   title    one short line
 *   message  one sentence of detail
 *   url      an in-app route that actually exists, or null
 */
abstract class ActivityNotification extends Notification
{
    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload($notifiable);
    }

    /** @return array<string, mixed> */
    abstract protected function payload(User $notifiable): array;

    /**
     * The same record lives at a different route for each role, so the link
     * is resolved against whoever is being notified rather than guessed.
     */
    protected function routeFor(User $notifiable, string $ownerPath, ?string $customerPath = null): ?string
    {
        if ($notifiable->role === 'owner') {
            return $ownerPath;
        }

        return $customerPath;
    }
}
