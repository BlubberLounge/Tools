<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartTournamentDetailResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'participants' => $this->whenLoaded('participants', function () {
                return $this->participants->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'seed_position' => $user->pivot->seed_position,
                        'final_placement' => $user->pivot->final_placement,
                        'eliminated' => $user->pivot->eliminated,
                        'wins' => $user->pivot->wins,
                        'losses' => $user->pivot->losses,
                        'points_for' => $user->pivot->points_for,
                        'points_against' => $user->pivot->points_against,
                    ];
                });
            }),
            'rounds' => $this->whenLoaded('rounds', function () {
                return $this->rounds->map(function ($round) {
                    return [
                        'id' => $round->id,
                        'round_number' => $round->round_number,
                        'name' => $round->name,
                        'status' => $round->status,
                        'matches' => DartTournamentMatchResource::collection($round->matches),
                    ];
                });
            }),
        ];
    }
}
