<?php
namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;

class ScoringService
{
    // public function storeThrow(DartGame $game, User $user, array $data)
    // {
    //     $value = $this->calculateValue($data['field'], $data['ring']);

    //     return $game->dartThrows()->create([
    //         'user_id' => $user->id,
    //         'set' => $data['set'],
    //         'leg' => $data['leg'],
    //         'turn' => $data['turn'],
    //         'throw' => $data['throw'],
    //         'field' => $data['field'],
    //         'ring' => $data['ring'],
    //         'value' => $value,
    //         'x' => $data['x'] ?? null,
    //         'y' => $data['y'] ?? null,
    //     ]);
    // }

    public function calculateValue(string $field, string $ring): int
    {
        return match ($ring) {
            'S' => (int) $field,
            'D' => (int) $field * 2,
            'T' => (int) $field * 3,
            'BULL' => 50,
            'SBULL' => 25,
            default => 0,
        };
    }
}
