<?php

namespace App\Services\Dart;

use App\Models\DartGameUser;
use App\Models\DartPlayerInvitation;
use App\Models\DartThrow;
use Illuminate\Support\Facades\DB;

class LocalPlayerResolver
{
    /**
     * Resolve a single local_player_id to a user_id via the invitations table.
     */
    public function resolve(string $localPlayerId): ?int
    {
        return DartPlayerInvitation::where('local_player_id', $localPlayerId)
            ->where('status', 'registered')
            ->whereNotNull('registered_user_id')
            ->value('registered_user_id');
    }

    /**
     * Batch-resolve local_player_ids to user_ids.
     *
     * @return array<string, int> Map of local_player_id => user_id (only resolved entries)
     */
    public function resolveMany(array $localPlayerIds): array
    {
        if (empty($localPlayerIds)) {
            return [];
        }

        return DartPlayerInvitation::whereIn('local_player_id', array_unique($localPlayerIds))
            ->where('status', 'registered')
            ->whereNotNull('registered_user_id')
            ->pluck('registered_user_id', 'local_player_id')
            ->toArray();
    }

    /**
     * Backfill user_id on all game_user and throw records for a local player.
     *
     * @return array{pivot_updated: int, throws_updated: int, conflicts_removed: int}
     */
    public function backfill(string $localPlayerId, int $userId): array
    {
        return DB::transaction(function () use ($localPlayerId, $userId) {
            // Find games where this local player has unresolved records
            $gameIds = DartGameUser::where('local_player_id', $localPlayerId)
                ->whereNull('user_id')
                ->pluck('dart_game_id');

            $conflictsRemoved = 0;

            if ($gameIds->isNotEmpty()) {
                // Find games where the user already has a record (would cause unique violation)
                $conflictingGameIds = DartGameUser::whereIn('dart_game_id', $gameIds)
                    ->where('user_id', $userId)
                    ->pluck('dart_game_id');

                if ($conflictingGameIds->isNotEmpty()) {
                    // Remove duplicate local player records where the user already exists
                    $conflictsRemoved = DartGameUser::where('local_player_id', $localPlayerId)
                        ->whereNull('user_id')
                        ->whereIn('dart_game_id', $conflictingGameIds)
                        ->delete();
                }
            }

            // Update remaining unresolved records
            $pivotUpdated = DartGameUser::where('local_player_id', $localPlayerId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            $throwsUpdated = DartThrow::where('local_player_id', $localPlayerId)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            return [
                'pivot_updated' => $pivotUpdated,
                'throws_updated' => $throwsUpdated,
                'conflicts_removed' => $conflictsRemoved,
            ];
        });
    }
}
