<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\DartGameStatus;
use App\Enums\DartGameUserStatus;
use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\DartGame;
use App\Models\User;
use App\Models\DartQueue;

class DartGameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['game'] = DartGame::with(['createdBy:id,name,firstname,lastname', 'users:id,name,firstname,lastname'])->find($id);

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $game = DartGame::find($id);
        $game->status = DartGameStatus::fromString($request->status) ?? $game->status;
        $game->save();

        $data = null;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DartGame $game)
    {
        //
    }

    /**
     *
     */
    public function updatePlace(Request $request, String $id, User $user)
    {
        $game = DartGame::find($id);
        $game->users()->updateExistingPivot($user->id, [
            'place' => $request->place
        ]);

        $data = null;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Display the specified resource.
     */
    public function showThrows(string $id)
    {
        $data['game'] = DartGame::with(['dartThrows', 'users:id,name,firstname,lastname'])->find($id);
        $data['user'] = Auth::user()->only('id');

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Display the specified resource by user.
     */
    public function showThrowsByUser(Request $request, string $id, User $user)
    {
        $game = DartGame::with(['dartThrows'])->find($id);
        $data['user'] = array_map(fn($user) => [$user['id'] => $game], $game->users->toArray());

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    public function getPlayerStatus(Request $request, String $id)
    {
        $data['user'] = array_map(fn($user) => ['user_id' => $user['id'], 'status' => $user['pivot']['status']], DartGame::find($id)->users->toArray());

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    public function destroyPlayer(Request $request, DartGame $dartGame, User $user)
    {
        // soft delete
        $dartGame->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

        // hard delete
        // $dartGame->users()->wherePivot('user_id', $user->id)->detach();

        return $this->sendResponse(null, 'ok');
    }

    /**
     *
     */
    public function accept(Request $request, DartGame $dartGame)
    {
        $game = Auth::user()->DartGames()->find($dartGame->id);

        if($game->pivot->status != DartGameUserStatus::PENDING->value)
            return $this->sendError('Can\'t update user game status', null, 409);

        $game->pivot->status = DartGameUserStatus::ACCEPTED;
        $game->pivot->save();

        return $this->sendResponse(null, 'ok');
    }

    /**
     *
     */
    public function decline(Request $request, DartGame $dartGame)
    {
        $game = Auth::user()->DartGames()->find($dartGame->id);

        if($game->pivot->status != DartGameUserStatus::PENDING->value)
        	return $this->sendError('Can\'t update user game status', null, 409);

        $game->pivot->status = DartGameUserStatus::DENIED;
        $game->pivot->save();

        return $this->sendResponse(null, 'ok');
   }

    /**
     *
     */
    public function queueAdd(Request $request)
    {
        $newQueue = new DartQueue();
        $newQueue->parent_user_id = Auth::user()->id;

        $newQueue->save();

        return $this->sendResponse(null, 'ok');
    }

    /**
     *
     */
    public function queueRemove(Request $request)
    {
        DartQueue::where('parent_user_id', Auth::user()->id)->delete();

        return $this->sendResponse(null, 'ok');
    }
}
