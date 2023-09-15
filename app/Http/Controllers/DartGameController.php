<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDartGameRequest;
use App\Http\Requests\UpdateDartGameRequest;
use Illuminate\Support\Facades\Auth;

use App\Enums\DartGameStatus;
use App\Enums\DartGameType;
use App\Models\DartGame;
use App\Models\DartX01Game;
use App\Models\DartAroundTheClockGame;
use App\Models\DartThrow;
use App\Models\User;

class DartGameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['users'] = User::all();
        return view('dart.game.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data['dartGameType'] = $request->type;
        // var_dump($request);

        return view('dart.game.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDartGameRequest $request)
    {
        // $data = $request->validated();
        // dd($request);

        $game = new DartGame();
        $game->created_by = Auth::user()->id;
        $game->type = DartGameType::X01;
        $game->status = DartGameStatus::CREATED;
        $game->private = $request->private ? 1 : 0;
        $game->title = $request->title ?? Auth::user()->getGameTitle();
        $game->comment = $request->comment ?? null;
        $game->points = $request->points ?? null;
        $game->start = $request->start ?? null;
        $game->end = $request->end ?? null;
        $game->singleOut = $request->singleOut ? 1 : 0;
        $game->doubleOut = $request->doubleOut ? 1 : 0;
        $game->trippleOut = $request->trippleOut ? 1 : 0;
        $game->singleIn = $request->singleIn ?? 1;
        $game->doubleIn = $request->doubleIn ?? 1;
        $game->trippleIn = $request->trippleIn ?? 1;
        $game->save();

        // create simple Hashmap
        foreach($request->input('users.*') as $user)
            $userPositons[$user] = $request->userPositions[$user];

        $users = User::findMany($request->input('users.*'));
        $users->each(fn($user) => $user->DartGames()->attach($game, ['position' => $userPositons[$user->id]]));

        return redirect()->route('dart.game.show', ['dartGame' => $game->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(DartGame $dartGame)
    {
        $data['id'] = $dartGame->id;
        $data['type'] = $dartGame->type;
        $data['status'] = $dartGame->status;
        $data['points'] = $dartGame->points;

        if($dartGame->isCreated() && $dartGame->allPlayersAccepted()) {
            $dartGame->status = DartGameStatus::RUNNING->value;
            $dartGame->save();
        }

        if($dartGame->isCreated()) {
            $data['users'] = $dartGame->usersBy('position')->get();
            $view = view('dart.game.waiting', $data);

        } else if($dartGame->isRunning()) {
            $data['users'] = $dartGame->usersBy('position')->get();
            $view = view('dart.game.show', $data);

        } else {
            $data['users'] = $dartGame->usersBy('place')->get();
            $data['game'] = $dartGame;
            $data['firstPlaceUser'] = $data['users'][0];
            $data['secondPlaceUser'] = $data['users'][1] ?? 'no player';
            $data['thirdPlaceUser'] = $data['users'][2] ?? 'no player';

            $mostMisthrows = $dartGame->getMostMisthrows();

            $data['stats'] = [
                'highestThrow' => $dartGame->getHighestThrowOfTurn(),
                'lowestThrow' => $dartGame->getLowestThrowOfTurn(),
                'bestTurn' => $dartGame->getUserHighestTurn(),
                'worstTurn' => $dartGame->getUserLowestTurn(),
                'highestAccuracy' => User::getRootUser()->full_name,
                'longestStreak' => $dartGame->getLongestStreak(),
                // 'highestStreak' => User::getRootUser()->full_name,
                'mostMisthrows' => $mostMisthrows['user'] ? $mostMisthrows : null,
                'winStreak' => User::getRootUser()->full_name,
                'loseStreak' => User::getRootUser()->full_name,
                'streak' => User::getRootUser()->full_name,
            ];

            $view = view('dart.game.showResult', $data);
        }

        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DartGame $dartGame)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDartGameRequest $request, DartGame $dartGame)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DartGame $dartGame)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function showLive(DartGame $dartGame)
    {
        //
    }
}
