<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartTournamentStandingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'wins' => $this->pivot->wins,
            'losses' => $this->pivot->losses,
            'points_for' => $this->pivot->points_for,
            'points_against' => $this->pivot->points_against,
            'eliminated' => $this->pivot->eliminated,
            'seed_position' => $this->pivot->seed_position,
            'final_placement' => $this->pivot->final_placement,
        ];
    }
}
