<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DartGameDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_game_id' => $this->client_game_id,
            'type' => $this->type,
            'game_type' => $this->game_type,
            'status' => $this->status,
            'title' => $this->title,
            'comment' => $this->comment,
            'points' => $this->points,
            'settings' => $this->settings,
            'options' => $this->settings,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'teams' => DartTeamResource::collection($this->whenLoaded('teams')),
            'throws' => DartGameDartThrowResource::collection($this->whenLoaded('dartThrows')),
        ];
    }
}
