<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartTournamentMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'round_id' => $this->dart_tournament_round_id,
            'match_number' => $this->match_number,
            'player1' => new UserResource($this->whenLoaded('player1')),
            'player2' => new UserResource($this->whenLoaded('player2')),
            'winner' => new UserResource($this->whenLoaded('winner')),
            'status' => $this->status,
            'bracket_position' => $this->bracket_position,
            'game_id' => $this->dart_game_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
