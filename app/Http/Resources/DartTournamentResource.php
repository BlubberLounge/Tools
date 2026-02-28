<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartTournamentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'format' => $this->format,
            'game_mode' => $this->game_mode,
            'game_options' => $this->game_options,
            'seed_type' => $this->seed_type,
            'best_of' => $this->best_of,
            'status' => $this->status,
            'winner' => new UserResource($this->whenLoaded('winner')),
            'created_by' => $this->created_by,
            'participant_count' => $this->whenCounted('participants'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
