<?php

namespace App\Services\Dart;

use App\Enums\DartTournamentMatchStatus;
use App\Enums\DartTournamentRoundStatus;
use App\Enums\DartTournamentStatus;
use App\Models\DartTournament;
use App\Models\DartTournamentMatch;
use App\Models\DartTournamentRound;

class TournamentBracketService
{
    /**
     * Seed a single elimination bracket.
     */
    public function seedSingleElimination(DartTournament $tournament): void
    {
        $participants = $tournament->participants()->get()->shuffle();
        $count = $participants->count();

        // Pad to next power of 2
        $bracketSize = 1;
        while ($bracketSize < $count) {
            $bracketSize *= 2;
        }

        $totalRounds = (int) log($bracketSize, 2);

        // Create all rounds
        $rounds = [];
        for ($r = 1; $r <= $totalRounds; $r++) {
            $name = $this->getSingleEliminationRoundName($r, $totalRounds);
            $rounds[$r] = DartTournamentRound::create([
                'dart_tournament_id' => $tournament->id,
                'round_number' => $r,
                'name' => $name,
                'status' => 'pending',
            ]);
        }

        // Assign seed positions
        foreach ($participants as $i => $participant) {
            $tournament->participants()->updateExistingPivot($participant->id, [
                'seed_position' => $i + 1,
            ]);
        }

        // Create first round matches
        $matchesInFirstRound = $bracketSize / 2;
        $slots = array_pad($participants->pluck('id')->toArray(), $bracketSize, null);

        $matchNumber = 1;
        for ($m = 0; $m < $matchesInFirstRound; $m++) {
            $p1 = $slots[$m * 2] ?? null;
            $p2 = $slots[$m * 2 + 1] ?? null;

            $isBye = ($p1 === null || $p2 === null);

            DartTournamentMatch::create([
                'dart_tournament_id' => $tournament->id,
                'dart_tournament_round_id' => $rounds[1]->id,
                'match_number' => $matchNumber++,
                'player1_id' => $p1,
                'player2_id' => $p2,
                'winner_id' => $isBye ? ($p1 ?? $p2) : null,
                'status' => $isBye ? 'bye' : 'ready',
                'bracket_position' => "R1-M{$m}",
            ]);
        }

        // Create placeholder matches for subsequent rounds
        for ($r = 2; $r <= $totalRounds; $r++) {
            $matchesInRound = $bracketSize / pow(2, $r);
            for ($m = 0; $m < $matchesInRound; $m++) {
                DartTournamentMatch::create([
                    'dart_tournament_id' => $tournament->id,
                    'dart_tournament_round_id' => $rounds[$r]->id,
                    'match_number' => $matchNumber++,
                    'player1_id' => null,
                    'player2_id' => null,
                    'status' => 'pending',
                    'bracket_position' => "R{$r}-M{$m}",
                ]);
            }
        }

        // Auto-advance bye winners to round 2
        $this->advanceByeWinners($tournament, $rounds[1], $rounds[2] ?? null);
    }

    /**
     * Seed a round robin schedule using the circle method.
     */
    public function seedRoundRobin(DartTournament $tournament): void
    {
        $participants = $tournament->participants()->get()->shuffle();
        $playerIds = $participants->pluck('id')->toArray();

        // Assign seed positions
        foreach ($playerIds as $i => $id) {
            $tournament->participants()->updateExistingPivot($id, [
                'seed_position' => $i + 1,
            ]);
        }

        $n = count($playerIds);
        $useGhost = ($n % 2 !== 0);

        if ($useGhost) {
            $playerIds[] = null; // ghost player for bye
            $n++;
        }

        $totalRounds = $n - 1;
        $matchesPerRound = $n / 2;
        $matchNumber = 1;

        // Circle method: fix first player, rotate rest
        $fixed = $playerIds[0];
        $rotating = array_slice($playerIds, 1);

        for ($r = 0; $r < $totalRounds; $r++) {
            $round = DartTournamentRound::create([
                'dart_tournament_id' => $tournament->id,
                'round_number' => $r + 1,
                'name' => 'Round ' . ($r + 1),
                'status' => 'pending',
            ]);

            // Build pairs for this round
            $pairs = [];
            $pairs[] = [$fixed, $rotating[0]];

            for ($m = 1; $m < $matchesPerRound; $m++) {
                $pairs[] = [$rotating[$m], $rotating[$n - 2 - $m]];
            }

            foreach ($pairs as $pair) {
                $p1 = $pair[0];
                $p2 = $pair[1];
                $isBye = ($p1 === null || $p2 === null);

                DartTournamentMatch::create([
                    'dart_tournament_id' => $tournament->id,
                    'dart_tournament_round_id' => $round->id,
                    'match_number' => $matchNumber++,
                    'player1_id' => $p1,
                    'player2_id' => $p2,
                    'winner_id' => $isBye ? ($p1 ?? $p2) : null,
                    'status' => $isBye ? 'bye' : 'ready',
                ]);
            }

            // Rotate: move last element to front of rotating array
            array_unshift($rotating, array_pop($rotating));
        }
    }

    /**
     * Advance the winner of a match to the next round (single elimination).
     */
    public function advanceWinner(DartTournament $tournament, DartTournamentMatch $match): void
    {
        if (!$match->winner_id || !$tournament->isSingleElimination()) {
            return;
        }

        $round = $match->round;
        $nextRound = $tournament->rounds()
            ->where('round_number', $round->round_number + 1)
            ->first();

        if (!$nextRound) {
            return; // Final round, no advancement needed
        }

        // Determine which next-round match this feeds into
        // bracket_position format: R{round}-M{match_index}
        preg_match('/R\d+-M(\d+)/', $match->bracket_position ?? '', $matches);
        $matchIndex = isset($matches[1]) ? (int) $matches[1] : 0;
        $nextMatchIndex = intdiv($matchIndex, 2);

        $nextMatch = $nextRound->matches()
            ->where('bracket_position', "R{$nextRound->round_number}-M{$nextMatchIndex}")
            ->first();

        if (!$nextMatch) {
            return;
        }

        // Place winner in the correct slot (even match index → player1, odd → player2)
        if ($matchIndex % 2 === 0) {
            $nextMatch->update(['player1_id' => $match->winner_id]);
        } else {
            $nextMatch->update(['player2_id' => $match->winner_id]);
        }

        // If both players are now set, mark as ready
        $nextMatch->refresh();
        if ($nextMatch->player1_id && $nextMatch->player2_id) {
            $nextMatch->update(['status' => 'ready']);
        }
    }

    /**
     * Check if all matches in a round are done and advance.
     */
    public function checkRoundComplete(DartTournament $tournament, DartTournamentRound $round): bool
    {
        $allDone = $round->matches()
            ->whereNotIn('status', [
                DartTournamentMatchStatus::DONE->value,
                DartTournamentMatchStatus::BYE->value,
            ])
            ->doesntExist();

        if (!$allDone) {
            return false;
        }

        $round->update(['status' => 'done']);

        // Start next round if exists
        $nextRound = $tournament->rounds()
            ->where('round_number', $round->round_number + 1)
            ->first();

        if ($nextRound) {
            $nextRound->update(['status' => 'running']);
        }

        return true;
    }

    /**
     * Check if the tournament is complete.
     */
    public function checkTournamentComplete(DartTournament $tournament): bool
    {
        if ($tournament->isSingleElimination()) {
            return $this->checkSingleEliminationComplete($tournament);
        }

        if ($tournament->isRoundRobin()) {
            return $this->checkRoundRobinComplete($tournament);
        }

        return false;
    }

    private function checkSingleEliminationComplete(DartTournament $tournament): bool
    {
        $lastRound = $tournament->rounds()->orderByDesc('round_number')->first();
        if (!$lastRound) return false;

        $finalMatch = $lastRound->matches()->first();
        if (!$finalMatch || $finalMatch->status !== DartTournamentMatchStatus::DONE) {
            return false;
        }

        $tournament->update([
            'status' => 'done',
            'winner_id' => $finalMatch->winner_id,
        ]);

        // Set final placements
        if ($finalMatch->winner_id) {
            $loserId = $finalMatch->winner_id == $finalMatch->player1_id
                ? $finalMatch->player2_id
                : $finalMatch->player1_id;

            $tournament->participants()->updateExistingPivot($finalMatch->winner_id, [
                'final_placement' => 1,
            ]);
            if ($loserId) {
                $tournament->participants()->updateExistingPivot($loserId, [
                    'final_placement' => 2,
                    'eliminated' => true,
                ]);
            }
        }

        return true;
    }

    private function checkRoundRobinComplete(DartTournament $tournament): bool
    {
        $allRoundsDone = $tournament->rounds()
            ->where('status', '!=', DartTournamentRoundStatus::DONE)
            ->doesntExist();

        if (!$allRoundsDone) return false;

        // Rank by wins, then points_for
        $ranked = $tournament->participants()
            ->orderByPivot('wins', 'desc')
            ->orderByPivot('points_for', 'desc')
            ->get();

        foreach ($ranked as $i => $participant) {
            $tournament->participants()->updateExistingPivot($participant->id, [
                'final_placement' => $i + 1,
            ]);
        }

        $winner = $ranked->first();

        $tournament->update([
            'status' => 'done',
            'winner_id' => $winner?->id,
        ]);

        return true;
    }

    /**
     * Auto-advance bye winners from a round to the next.
     */
    private function advanceByeWinners(DartTournament $tournament, DartTournamentRound $round, ?DartTournamentRound $nextRound): void
    {
        if (!$nextRound) return;

        $byeMatches = $round->matches()
            ->where('status', DartTournamentMatchStatus::BYE)
            ->whereNotNull('winner_id')
            ->get();

        foreach ($byeMatches as $match) {
            $this->advanceWinner($tournament, $match);
        }
    }

    private function getSingleEliminationRoundName(int $roundNumber, int $totalRounds): string
    {
        $roundsFromEnd = $totalRounds - $roundNumber;

        return match ($roundsFromEnd) {
            0 => 'Final',
            1 => 'Semi-Finals',
            2 => 'Quarter-Finals',
            default => 'Round ' . $roundNumber,
        };
    }
}
