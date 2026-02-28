<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Resources\DartLocalPlayerResource;
use App\Models\DartLocalPlayer;
use Illuminate\Http\Request;

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
            'name' => ['required', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $player = DartLocalPlayer::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'avatar_url' => $validated['avatar_url'] ?? null,
        ]);

        return response()->json(new DartLocalPlayerResource($player), 201);
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
