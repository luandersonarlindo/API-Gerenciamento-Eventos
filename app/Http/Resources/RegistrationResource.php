<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registered_at' => $this->created_at?->toDateTimeString(),
            'event' => new EventResource($this->whenLoaded('event')),
        ];
    }
}
