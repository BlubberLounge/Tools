<?php
namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;

class ScoringService
{
    public static function polarToCartesian(float $r, float $theta): array
    {
        return [
            'x' => round($r * cos($theta), 4),
            'y' => round($r * sin($theta), 4),
        ];
    }

    /**
     * Calculate the point value of a dart throw.
     *
     * Valid rings: S (single), D (double), T (triple), O (outside/miss).
     * Bullseye: field=25, ring=S → 25pts (outer bull), ring=D → 50pts (inner bull).
     */
    public function calculateValue(string $field, string $ring): int
    {
        return match ($ring) {
            'S' => (int) $field,
            'D' => (int) $field * 2,
            'T' => (int) $field * 3,
            'O' => 0,
            default => 0,
        };
    }
}
