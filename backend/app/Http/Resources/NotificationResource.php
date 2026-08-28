<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Flattens Laravel's database notification into the shape the bell renders.
 *
 * The `data` column is the payload the notification class wrote, so the
 * fields below are read out of it rather than off the model.
 */
class NotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => $data['type'] ?? 'activity',
            'title' => $data['title'] ?? 'Activity',
            'message' => $data['message'] ?? '',
            'url' => $data['url'] ?? null,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
