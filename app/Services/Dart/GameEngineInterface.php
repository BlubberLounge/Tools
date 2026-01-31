<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;
use App\Models\DartThrow;

interface GameEngineInterface
{
    /**
     * Startet das Spiel (Initialisierung).
     */
    public function start(DartGame $game): array;

    /**
     * Verarbeitet einen eingehenden Wurf vom angegebenen User.
     * Gibt den aktuellen Spielzustand zurück.
     */
    public function submitThrow(DartGame $game, User $user, array $payload): array;

    /**
     * Gibt aktuellen Spielzustand als array zurück (für Display/ESP32).
     */
    public function getState(DartGame $game): array;

    /**
     * Prüft ob das Spiel beendet ist.
     */
    public function isFinished(DartGame $game): bool;

    /**
     * Optional: erlaubt Engine-spezifische Cleanup-Arbeiten.
     */
    public function finish(DartGame $game): void;
}
