<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\DartGameStatus;
use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\DartGame;
use App\Models\User;

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
}
