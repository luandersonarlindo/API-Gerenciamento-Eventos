<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $registered = $this->registrations_count ?? $this->registrations()->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'event_time' => $this->event_time ? substr($this->event_time, 0, 5) : null,
            'max_participants' => $this->max_participants,
            'registered_participants' => $registered,
            // null quando o evento não tem limite de participantes
            'available_spots' => $this->max_participants === null
                ? null
                : max(0, $this->max_participants - $registered),
            'organizer' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
