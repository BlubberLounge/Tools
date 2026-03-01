<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Resources\DartLocalPlayerResource;
use App\Models\DartLocalPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DartLocalPlayerController extends Controller
{
    public function index(Request $request)
    {
        $players = DartLocalPlayer::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return response()->json(DartLocalPlayerResource::collection($players));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['nullable', 'string', 'uuid', 'max:36'],
            'name' => ['required', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
            'statistics' => ['nullable', 'array'],
        ]);

        $attrs = [
            'name' => $validated['name'],
            'avatar_url' => $validated['avatar_url'] ?? null,
            'statistics' => $validated['statistics'] ?? null,
        ];

        if (!empty($validated['id'])) {
            // Client provided an id — upsert by id + user_id
            $player = DartLocalPlayer::withTrashed()
                ->where('id', $validated['id'])
                ->where('user_id', $request->user()->id)
                ->first();

            if ($player) {
                $player->restore();
                $player->update($attrs);
            } else {
                // ID might already exist for a different user (account switch on same device)
                $idTaken = DartLocalPlayer::withTrashed()
                    ->where('id', $validated['id'])
                    ->exists();

                $player = new DartLocalPlayer($attrs);
                $player->id = $idTaken ? (string) Str::uuid() : $validated['id'];
                $player->user_id = $request->user()->id;
                $player->save();
            }
        } else {
            // No client id — create with auto-generated UUID
            $player = DartLocalPlayer::create([
                'user_id' => $request->user()->id,
                ...$attrs,
            ]);
        }

        $status = $player->wasRecentlyCreated ? 201 : 200;
        return response()->json(new DartLocalPlayerResource($player), $status);
    }

    public function update(Request $request, DartLocalPlayer $localPlayer)
    {
        if ($localPlayer->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $localPlayer->update($validated);

        return response()->json(new DartLocalPlayerResource($localPlayer));
    }

    public function destroy(Request $request, DartLocalPlayer $localPlayer)
    {
        if ($localPlayer->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $localPlayer->delete();

        return response()->json(['success' => true]);
    }
}
