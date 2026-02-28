<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'position' => $this->position,
            'place' => $this->place,
            'players' => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
