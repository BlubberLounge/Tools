<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartGameDartThrowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'field' => $this->field,
            'ring' => $this->ring,
            'x' => $this->x,
            'y' => $this->y,
            'r' => $this->r,
            'theta' => $this->theta,
        ];
    }
}
